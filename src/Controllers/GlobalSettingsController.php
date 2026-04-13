<?php

namespace MultilingualFormsFluentFormsWpml\Controllers;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class GlobalSettingsController
{
    const GLOBAL_STRINGS_SCHEMA_VERSION = '2';

    private $package = [
        'kind'  => 'Fluent Forms Global',
        'name'  => 'global_settings',
        'title' => 'Fluent Forms Global Settings',
    ];

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        add_action('init', [$this, 'maybeRegisterGlobalStrings'], 20);
        add_action('added_option', [$this, 'markGlobalStringsDirty'], 10, 2);
        add_action('updated_option', [$this, 'markGlobalStringsDirty'], 10, 3);

        add_filter('option__fluentform_global_form_settings', [$this, 'translateGlobalFormSettings']);
        add_filter('option__fluentform_double_optin_settings', [$this, 'translateGlobalDoubleOptinSettings']);
        add_filter('option___fluentform_payment_module_settings', [$this, 'translateGlobalPaymentModuleSettings']);
        add_filter('option_fluentform_payment_settings_test', [$this, 'translateOfflinePaymentSettings']);
        add_filter('fluentform/double_optin_invalid_confirmation_url_message', [$this, 'translateDoubleOptinInvalidConfirmationUrlMessage']);
    }

    public function maybeRegisterGlobalStrings()
    {
        if (!$this->shouldRegisterGlobalStrings()) {
            return;
        }

        $this->registerOptionStrings(
            (array) get_option('_fluentform_global_form_settings', []),
            $this->extractGlobalFormSettingStrings()
        );

        $this->registerOptionStrings(
            (array) get_option('_fluentform_double_optin_settings', []),
            $this->extractGlobalDoubleOptinStrings()
        );

        $this->registerOptionStrings(
            (array) get_option('__fluentform_payment_module_settings', []),
            $this->extractGlobalPaymentModuleStrings()
        );

        $this->registerOptionStrings(
            (array) get_option('fluentform_payment_settings_test', []),
            $this->extractOfflinePaymentStrings()
        );

        update_option($this->getGlobalSyncVersionOptionName(), self::GLOBAL_STRINGS_SCHEMA_VERSION, false);
        update_option($this->getGlobalDirtyOptionName(), '0', false);
    }

    public function markGlobalStringsDirty($option)
    {
        if (!$this->isTrackedOption($option)) {
            return;
        }

        update_option($this->getGlobalDirtyOptionName(), '1', false);
    }

    public function translateGlobalFormSettings($settings)
    {
        if (!is_array($settings)) {
            return $settings;
        }

        $keys = $this->extractGlobalFormSettingStrings();

        foreach ($keys as $path => $translationKey) {
            $value = $this->arrayGet($settings, $path);
            if (is_string($value) && $value !== '') {
                $this->arraySet(
                    $settings,
                    $path,
                    apply_filters('wpml_translate_string', $value, $translationKey, $this->package)
                );
            }
        }

        return $settings;
    }

    public function translateGlobalDoubleOptinSettings($settings)
    {
        if (!is_array($settings)) {
            return $settings;
        }

        $keys = $this->extractGlobalDoubleOptinStrings();

        foreach ($keys as $path => $translationKey) {
            $value = $this->arrayGet($settings, $path);
            if (is_string($value) && $value !== '') {
                $this->arraySet(
                    $settings,
                    $path,
                    apply_filters('wpml_translate_string', $value, $translationKey, $this->package)
                );
            }
        }

        return $settings;
    }

    public function translateOfflinePaymentSettings($settings)
    {
        if (!is_array($settings)) {
            return $settings;
        }

        $keys = $this->extractOfflinePaymentStrings();

        foreach ($keys as $path => $translationKey) {
            $value = $this->arrayGet($settings, $path);
            if (is_string($value) && $value !== '') {
                $this->arraySet(
                    $settings,
                    $path,
                    apply_filters('wpml_translate_string', $value, $translationKey, $this->package)
                );
            }
        }

        return $settings;
    }

    public function translateGlobalPaymentModuleSettings($settings)
    {
        if (!is_array($settings)) {
            return $settings;
        }

        $keys = $this->extractGlobalPaymentModuleStrings();

        foreach ($keys as $path => $translationKey) {
            $value = $this->arrayGet($settings, $path);
            if (is_string($value) && $value !== '') {
                $this->arraySet(
                    $settings,
                    $path,
                    apply_filters('wpml_translate_string', $value, $translationKey, $this->package)
                );
            }
        }

        return $settings;
    }

    public function translateDoubleOptinInvalidConfirmationUrlMessage($message)
    {
        $translationKey = 'global_double_optin_invalid_confirmation_url_message';
        $type = (strpos($message, '<') !== false || strpos($message, '{') !== false || strpos($message, '#') !== false)
            ? 'AREA'
            : 'LINE';

        do_action('wpml_register_string', $message, $translationKey, $this->package, 0, $type);

        return apply_filters('wpml_translate_string', $message, $translationKey, $this->package);
    }

    private function registerOptionStrings(array $settings, array $pathMap)
    {
        foreach ($pathMap as $path => $translationKey) {
            $value = $this->arrayGet($settings, $path);
            if (!is_string($value) || $value === '') {
                continue;
            }

            $type = (strpos($value, '<') !== false || strpos($value, '{') !== false || strpos($value, '#') !== false)
                ? 'AREA'
                : 'LINE';

            do_action('wpml_register_string', $value, $translationKey, $this->package, 0, $type);
        }
    }

    private function shouldRegisterGlobalStrings()
    {
        if (get_option($this->getGlobalSyncVersionOptionName()) !== self::GLOBAL_STRINGS_SCHEMA_VERSION) {
            return true;
        }

        return get_option($this->getGlobalDirtyOptionName(), '1') === '1';
    }

    private function isTrackedOption($option)
    {
        return in_array($option, [
            '_fluentform_global_form_settings',
            '_fluentform_double_optin_settings',
            '__fluentform_payment_module_settings',
            'fluentform_payment_settings_test',
        ], true);
    }

    private function getGlobalSyncVersionOptionName()
    {
        return '_mfffwpml_global_strings_schema_version';
    }

    private function getGlobalDirtyOptionName()
    {
        return '_mfffwpml_global_strings_dirty';
    }

    private function extractGlobalFormSettingStrings()
    {
        return [
            'default_messages.required' => 'global_default_message_required',
            'default_messages.email' => 'global_default_message_email',
            'default_messages.numeric' => 'global_default_message_numeric',
            'default_messages.min' => 'global_default_message_min',
            'default_messages.max' => 'global_default_message_max',
            'default_messages.digits' => 'global_default_message_digits',
            'default_messages.url' => 'global_default_message_url',
            'default_messages.allowed_image_types' => 'global_default_message_allowed_image_types',
            'default_messages.allowed_file_types' => 'global_default_message_allowed_file_types',
            'default_messages.max_file_size' => 'global_default_message_max_file_size',
            'default_messages.max_file_count' => 'global_default_message_max_file_count',
            'default_messages.valid_phone_number' => 'global_default_message_valid_phone_number',
        ];
    }

    private function extractGlobalDoubleOptinStrings()
    {
        return [
            'email_subject' => 'global_double_optin_email_subject',
            'email_body' => 'global_double_optin_email_body',
        ];
    }

    private function extractOfflinePaymentStrings()
    {
        return [
            'payment_instruction' => 'global_payment_settings_test_instruction',
        ];
    }

    private function extractGlobalPaymentModuleStrings()
    {
        return [
            'business_name' => 'global_payment_module_business_name',
            'business_address' => 'global_payment_module_business_address',
        ];
    }

    private function arrayGet(array $array, $path)
    {
        $segments = explode('.', $path);
        $value = $array;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function arraySet(array &$array, $path, $newValue)
    {
        $segments = explode('.', $path);
        $target = &$array;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $newValue;
    }
}
