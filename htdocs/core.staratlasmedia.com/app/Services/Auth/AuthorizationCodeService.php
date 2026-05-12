<?php

namespace App\Services\Auth;

use App\Models\AuthAuthorizationCode;
use App\Models\BridgeInstallation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthorizationCodeService
{
    /**
     * @return array{code: string, record: AuthAuthorizationCode}
     */
    public function create(
        User $user,
        Site $site,
        ?BridgeInstallation $bridgeInstallation,
        string $origin,
        string $redirectUri,
        string $state,
        ?string $nonce = null,
        ?int $ttlSeconds = null,
    ): array {
        $code = bin2hex(random_bytes(32));

        $record = AuthAuthorizationCode::query()->create([
            'code_hash' => hash('sha256', $code),
            'user_id' => $user->id,
            'site_id' => $site->id,
            'site_origin_id' => $bridgeInstallation?->site_origin_id,
            'bridge_installation_id' => $bridgeInstallation?->id,
            'origin' => $origin,
            'redirect_url' => $redirectUri,
            'redirect_uri' => $redirectUri,
            'state_hash' => hash('sha256', $state),
            'nonce_hash' => hash('sha256', $nonce ?? ''),
            'status' => 'active',
            'expires_at' => now()->addSeconds($ttlSeconds ?? (int) config('core.auth.authorization_code_ttl_seconds', 120)),
            'metadata' => [
                'phase' => 'phase_7_skeleton',
            ],
        ]);

        return ['code' => $code, 'record' => $record];
    }

    public function consume(string $code, BridgeInstallation $bridgeInstallation): ?AuthAuthorizationCode
    {
        return DB::transaction(function () use ($code, $bridgeInstallation): ?AuthAuthorizationCode {
            /** @var AuthAuthorizationCode|null $record */
            $record = AuthAuthorizationCode::query()
                ->where('code_hash', hash('sha256', $code))
                ->where('bridge_installation_id', $bridgeInstallation->id)
                ->lockForUpdate()
                ->first();

            if ($record === null || $record->status !== 'active' || $record->consumed_at !== null || $record->expires_at->isPast()) {
                return null;
            }

            $record->forceFill([
                'status' => 'consumed',
                'consumed_at' => now(),
            ])->save();

            return $record->fresh(['user', 'site', 'bridgeInstallation']);
        });
    }
}
