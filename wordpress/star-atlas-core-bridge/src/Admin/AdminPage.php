<?php

declare(strict_types=1);

namespace StarAtlas\CoreBridge\Admin;

use StarAtlas\CoreBridge\Api\CoreClient;
use StarAtlas\CoreBridge\Setup\SetupService;
use StarAtlas\CoreBridge\Update\UpdateClient;
use StarAtlas\CoreBridge\Utils\Options;
use StarAtlas\CoreBridge\Utils\UrlResolver;

final class AdminPage
{
    public function __construct(
        private readonly Options $options,
        private readonly UrlResolver $resolver,
        private readonly SetupService $setup,
        private readonly CoreClient $client,
    ) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_star_atlas_core_bridge', [$this, 'handle']);
    }

    public function menu(): void
    {
        add_menu_page(
            __('Star Atlas Core', 'star-atlas-core-bridge'),
            __('Star Atlas Core', 'star-atlas-core-bridge'),
            'manage_options',
            'star-atlas-core',
            [$this, 'render'],
            'dashicons-networking'
        );
    }

    public function handle(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage Star Atlas Core Bridge.', 'star-atlas-core-bridge'));
        }

        check_admin_referer('star_atlas_core_bridge_action');

        $task = isset($_POST['bridge_task']) ? sanitize_key((string) wp_unslash($_POST['bridge_task'])) : '';
        $message = 'updated';

        if ($task === 'claim') {
            $token = isset($_POST['setup_token']) ? sanitize_text_field((string) wp_unslash($_POST['setup_token'])) : '';
            $result = $token === '' ? ['ok' => false, 'message' => 'Setup token is required.'] : $this->setup->claim($token);
            $message = ($result['ok'] ?? false) === true ? 'configured' : 'error';
            set_transient('star_atlas_core_bridge_admin_notice', $result['message'] ?? $message, 60);
        } elseif ($task === 'refresh') {
            $result = $this->setup->refresh();
            $message = ($result['ok'] ?? false) === true ? 'refreshed' : 'error';
            set_transient('star_atlas_core_bridge_admin_notice', $result['message'] ?? $message, 60);
        } elseif ($task === 'test') {
            $result = $this->client->heartbeat();
            $this->options->update([
                'last_connection_check' => gmdate('c'),
                'connection_status' => ($result['ok'] ?? false) === true ? 'ok' : 'error',
            ]);
            $message = ($result['ok'] ?? false) === true ? 'connection-ok' : 'error';
            set_transient('star_atlas_core_bridge_admin_notice', $result['message'] ?? $message, 60);
        } elseif ($task === 'updates') {
            $result = (new UpdateClient($this->options, $this->client, $this->resolver))->check();
            $message = ($result['ok'] ?? false) === true ? 'updates-checked' : 'error';
            set_transient('star_atlas_core_bridge_admin_notice', $result['message'] ?? $message, 60);
        } elseif ($task === 'settings') {
            $channel = isset($_POST['release_channel']) ? sanitize_key((string) wp_unslash($_POST['release_channel'])) : 'stable';
            $this->options->update([
                'release_channel' => in_array($channel, ['stable', 'beta', 'internal'], true) ? $channel : 'stable',
                'update_checks_disabled' => isset($_POST['update_checks_disabled']),
            ]);
            $message = 'settings-saved';
        } elseif ($task === 'reset') {
            $this->options->reset();
            $message = 'reset';
        }

        wp_safe_redirect(add_query_arg(['page' => 'star-atlas-core', 'bridge_message' => $message], admin_url('admin.php')));
        exit;
    }

    public function render(): void
    {
        $options = $this->options->all();
        $notice = get_transient('star_atlas_core_bridge_admin_notice');
        delete_transient('star_atlas_core_bridge_admin_notice');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Star Atlas Core', 'star-atlas-core-bridge'); ?></h1>
            <?php if (is_string($notice) && $notice !== '') : ?>
                <div class="notice notice-info"><p><?php echo esc_html($notice); ?></p></div>
            <?php endif; ?>

            <h2><?php echo esc_html__('Configuration Status', 'star-atlas-core-bridge'); ?></h2>
            <table class="widefat striped" style="max-width: 900px;">
                <tbody>
                    <tr><th><?php echo esc_html__('Status', 'star-atlas-core-bridge'); ?></th><td><?php echo esc_html($this->options->configured() ? 'Configured' : 'Not configured'); ?></td></tr>
                    <tr><th><?php echo esc_html__('Detected origin', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html($this->resolver->detectedOrigin()); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Detected base path', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html($this->resolver->detectedBasePath()); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Installation ID', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html((string) $options['bridge_installation_id']); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Secret fingerprint', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html($this->mask((string) $options['bridge_secret_fingerprint'])); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Current plugin version', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html(STAR_ATLAS_CORE_BRIDGE_VERSION); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Latest available version', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html((string) $options['latest_version']); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Release channel', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html($this->options->releaseChannel()); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Last update check', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html((string) $options['last_update_check']); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Update status', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html((string) $options['update_status']); ?></code></td></tr>
                    <tr><th><?php echo esc_html__('Last connection check', 'star-atlas-core-bridge'); ?></th><td><code><?php echo esc_html((string) $options['last_connection_check']); ?></code></td></tr>
                </tbody>
            </table>

            <h2><?php echo esc_html__('Setup Token', 'star-atlas-core-bridge'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('star_atlas_core_bridge_action'); ?>
                <input type="hidden" name="action" value="star_atlas_core_bridge">
                <input type="hidden" name="bridge_task" value="claim">
                <input type="password" name="setup_token" class="regular-text" autocomplete="off">
                <?php submit_button(__('Connect to Core', 'star-atlas-core-bridge'), 'primary', 'submit', false); ?>
            </form>

            <h2><?php echo esc_html__('Actions', 'star-atlas-core-bridge'); ?></h2>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <?php $this->button('refresh', __('Refresh config', 'star-atlas-core-bridge')); ?>
                <?php $this->button('test', __('Test connection', 'star-atlas-core-bridge')); ?>
                <?php $this->button('updates', __('Check for updates', 'star-atlas-core-bridge')); ?>
                <?php $this->button('reset', __('Reset config', 'star-atlas-core-bridge'), 'delete'); ?>
            </div>

            <h2><?php echo esc_html__('Update Settings', 'star-atlas-core-bridge'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('star_atlas_core_bridge_action'); ?>
                <input type="hidden" name="action" value="star_atlas_core_bridge">
                <input type="hidden" name="bridge_task" value="settings">
                <select name="release_channel">
                    <?php foreach (['stable', 'beta', 'internal'] as $channel) : ?>
                        <option value="<?php echo esc_attr($channel); ?>" <?php selected($this->options->releaseChannel(), $channel); ?>><?php echo esc_html($channel); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>
                    <input type="checkbox" name="update_checks_disabled" value="1" <?php checked($this->options->updateChecksDisabled()); ?>>
                    <?php echo esc_html__('Disable update checks for debugging', 'star-atlas-core-bridge'); ?>
                </label>
                <?php submit_button(__('Save settings', 'star-atlas-core-bridge'), 'secondary', 'submit', false); ?>
            </form>
        </div>
        <?php
    }

    private function button(string $task, string $label, string $class = 'secondary'): void
    {
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('star_atlas_core_bridge_action'); ?>
            <input type="hidden" name="action" value="star_atlas_core_bridge">
            <input type="hidden" name="bridge_task" value="<?php echo esc_attr($task); ?>">
            <?php submit_button($label, $class, 'submit', false); ?>
        </form>
        <?php
    }

    private function mask(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return substr($value, 0, 8).'...'.substr($value, -6);
    }
}
