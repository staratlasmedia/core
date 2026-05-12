<?php

namespace App\Services\Auth;

use App\Models\AuthAuthorizationCode;

class IdentityPayloadBuilder
{
    public function __construct(private readonly PpidService $ppids) {}

    /**
     * @return array<string, mixed>
     */
    public function forAuthorizationCode(AuthAuthorizationCode $code): array
    {
        $user = $code->user;
        $site = $code->site;
        $sitePpid = $this->ppids->siteScoped($user, $site);
        $networkPpid = $this->ppids->networkScoped($user);

        return [
            'user' => [
                'ppid' => $sitePpid->ppid,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'email_verified' => $user->email_verified_at !== null,
            ],
            'session' => [
                'site_code' => $site->code,
                'site_ppid' => $sitePpid->ppid,
                'network_ppid_available' => $networkPpid->exists,
            ],
            'expires_in' => 300,
        ];
    }
}
