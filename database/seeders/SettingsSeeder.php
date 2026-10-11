<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Company
            ['group' => 'company', 'key' => 'name', 'value' => 'Monnemtisch Catering', 'type' => 'string'],
            ['group' => 'company', 'key' => 'legal_name', 'value' => 'Anatolian Gourmet GmbH', 'type' => 'string'],
            ['group' => 'company', 'key' => 'tagline', 'value' => 'Premium Catering für besondere Momente', 'type' => 'string'],
            ['group' => 'company', 'key' => 'vat_id', 'value' => '', 'type' => 'string'],
            ['group' => 'general', 'key' => 'legal_name', 'value' => 'Anatolian Gourmet GmbH', 'type' => 'string'],
            ['group' => 'general', 'key' => 'company_name', 'value' => 'Monnemtisch Catering', 'type' => 'string'],
            ['group' => 'general', 'key' => 'logo', 'value' => 'images/logo-monnemtisch.png', 'type' => 'string'],
            ['group' => 'legal', 'key' => 'tax_id', 'value' => '38180/25043', 'type' => 'string'],

            // Contact
            ['group' => 'contact', 'key' => 'phone', 'value' => '+49 711 12345678', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'email', 'value' => 'info@monnemtisch.de', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'whatsapp', 'value' => '+4915112345678', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'address', 'value' => 'H1, 10', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'street', 'value' => 'H1, 10', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'postal_code', 'value' => '68159', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'city', 'value' => 'Mannheim', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'country', 'value' => 'Deutschland', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'region', 'value' => 'Mannheim und Umgebung', 'type' => 'string'],
            ['group' => 'contact', 'key' => 'tax_id', 'value' => '38180/25043', 'type' => 'string'],

            // Theme colors
            ['group' => 'theme', 'key' => 'primary', 'value' => '#2C2C2C', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'secondary', 'value' => '#F5F0E8', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'accent', 'value' => '#C9A227', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'background', 'value' => '#F5F0E8', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'text', 'value' => '#2C2C2C', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'muted', 'value' => '#6B6B6B', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'charcoal', 'value' => '#2C2C2C', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'cream', 'value' => '#F5F0E8', 'type' => 'string'],
            ['group' => 'theme', 'key' => 'gold', 'value' => '#C9A227', 'type' => 'string'],

            // Business / tax
            ['group' => 'business', 'key' => 'tax_rate', 'value' => '7', 'type' => 'float'],
            ['group' => 'business', 'key' => 'currency', 'value' => 'EUR', 'type' => 'string'],
            ['group' => 'business', 'key' => 'timezone', 'value' => 'Europe/Berlin', 'type' => 'string'],
            ['group' => 'business', 'key' => 'locale', 'value' => 'de', 'type' => 'string'],
            ['group' => 'business', 'key' => 'min_order', 'value' => '150', 'type' => 'float'],
            ['group' => 'business', 'key' => 'lead_time_hours', 'value' => '48', 'type' => 'integer'],

            // Hero
            ['group' => 'hero', 'key' => 'title', 'value' => 'Exquisites Catering für Ihre Feier', 'type' => 'string'],
            ['group' => 'hero', 'key' => 'subtitle', 'value' => 'Von eleganten Buffets bis Fingerfood – Monnemtisch Catering bringt Genuss nach Mannheim und Umgebung.', 'type' => 'string'],
            ['group' => 'hero', 'key' => 'cta_primary', 'value' => 'Angebot anfordern', 'type' => 'string'],
            ['group' => 'hero', 'key' => 'cta_secondary', 'value' => 'Menü entdecken', 'type' => 'string'],
            ['group' => 'hero', 'key' => 'image', 'value' => '/images/placeholders/hero.svg', 'type' => 'string'],

            // Trust bar
            ['group' => 'trust', 'key' => 'years_experience', 'value' => '12', 'type' => 'integer'],
            ['group' => 'trust', 'key' => 'events_count', 'value' => '850', 'type' => 'integer'],
            ['group' => 'trust', 'key' => 'rating', 'value' => '4.9', 'type' => 'float'],
            ['group' => 'trust', 'key' => 'review_count', 'value' => '240', 'type' => 'integer'],
            ['group' => 'trust', 'key' => 'tagline', 'value' => 'Premium Catering aus Leidenschaft', 'type' => 'string'],
            ['group' => 'trust', 'key' => 'usp', 'value' => json_encode([
                'Frische Zutaten aus der Region',
                'Pünktliche Lieferung in Mannheim',
                'Flexible Menügestaltung',
                'Persönliche Beratung',
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],
            ['group' => 'trust', 'key' => 'items', 'value' => json_encode([
                ['label' => '12+ Jahre Erfahrung', 'value' => '12+'],
                ['label' => '850+ Events', 'value' => '850+'],
                ['label' => '4,9 Sterne', 'value' => '4,9'],
                ['label' => '240+ Bewertungen', 'value' => '240+'],
            ], JSON_UNESCAPED_UNICODE), 'type' => 'json'],

            // Social
            ['group' => 'social', 'key' => 'facebook', 'value' => 'https://facebook.com/monnemtischcatering', 'type' => 'string'],
            ['group' => 'social', 'key' => 'instagram', 'value' => 'https://instagram.com/monnemtischcatering', 'type' => 'string'],
            ['group' => 'social', 'key' => 'linkedin', 'value' => 'https://linkedin.com/company/monnemtisch-catering', 'type' => 'string'],
            ['group' => 'social', 'key' => 'youtube', 'value' => '', 'type' => 'string'],
            ['group' => 'social', 'key' => 'tiktok', 'value' => '', 'type' => 'string'],

            // SMTP defaults as settings keys
            ['group' => 'smtp', 'key' => 'mailer', 'value' => 'smtp', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'host', 'value' => 'smtp.mailtrap.io', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'port', 'value' => '587', 'type' => 'integer'],
            ['group' => 'smtp', 'key' => 'username', 'value' => '', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'password', 'value' => '', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'encryption', 'value' => 'tls', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'from_address', 'value' => 'info@monnemtisch.de', 'type' => 'string'],
            ['group' => 'smtp', 'key' => 'from_name', 'value' => 'Monnemtisch Catering', 'type' => 'string'],

            // Analytics / pixels (empty)
            ['group' => 'analytics', 'key' => 'google_analytics_id', 'value' => '', 'type' => 'string'],
            ['group' => 'analytics', 'key' => 'google_tag_manager_id', 'value' => '', 'type' => 'string'],
            ['group' => 'analytics', 'key' => 'facebook_pixel_id', 'value' => '', 'type' => 'string'],
            ['group' => 'analytics', 'key' => 'google_ads_id', 'value' => '', 'type' => 'string'],

            // SEO defaults
            ['group' => 'seo', 'key' => 'meta_title', 'value' => 'Catering Mannheim – Buffets & Fingerfood | Monnemtisch', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'meta_description', 'value' => 'Catering in Mannheim: Buffets, Fingerfood, Frühstück und Grillcatering ab 10 Personen. Festpreis, frei kombinierbar – unverbindlich anfragen.', 'type' => 'string'],
            ['group' => 'seo', 'key' => 'meta_keywords', 'value' => 'Catering Mannheim, Buffet Catering Mannheim, Fingerfood Catering Mannheim, Frühstück Catering, Grillcatering Mannheim', 'type' => 'string'],

            ['group' => 'stripe', 'key' => 'enabled', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'stripe', 'key' => 'mode', 'value' => 'test', 'type' => 'string'],
            ['group' => 'stripe', 'key' => 'public_key', 'value' => '', 'type' => 'string'],
            ['group' => 'stripe', 'key' => 'secret_key', 'value' => '', 'type' => 'string'],
            ['group' => 'stripe', 'key' => 'webhook_secret', 'value' => '', 'type' => 'string'],

            ['group' => 'paypal', 'key' => 'enabled', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'paypal', 'key' => 'mode', 'value' => 'sandbox', 'type' => 'string'],
            ['group' => 'paypal', 'key' => 'client_id', 'value' => '', 'type' => 'string'],
            ['group' => 'paypal', 'key' => 'secret', 'value' => '', 'type' => 'string'],
            ['group' => 'paypal', 'key' => 'webhook_id', 'value' => '', 'type' => 'string'],

            ['group' => 'payments', 'key' => 'anfrage', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'payments', 'key' => 'rechnung', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'payments', 'key' => 'vorkasse', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'payments', 'key' => 'bar', 'value' => '1', 'type' => 'boolean'],

            ['group' => 'maintenance', 'key' => 'enabled', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'maintenance', 'key' => 'title', 'value' => 'Wir sind bald für Sie da', 'type' => 'string'],
            ['group' => 'maintenance', 'key' => 'message', 'value' => 'Unsere Website befindet sich gerade im Aufbau. Monnemtisch Catering in Mannheim – bald wieder für Sie erreichbar.', 'type' => 'string'],

            ['group' => 'notifications', 'key' => 'admin_email', 'value' => 'info@monnemtisch.de', 'type' => 'string'],
            ['group' => 'notifications', 'key' => 'email_customer_order_paid', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'email_admin_order', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'email_admin_order_paid', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'email_admin_quote', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'email_admin_contact', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'telegram_enabled', 'value' => '0', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'telegram_bot_token', 'value' => '', 'type' => 'string'],
            ['group' => 'notifications', 'key' => 'telegram_chat_id', 'value' => '', 'type' => 'string'],
            ['group' => 'notifications', 'key' => 'telegram_on_order', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'telegram_on_order_paid', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'telegram_on_quote', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'notifications', 'key' => 'telegram_on_contact', 'value' => '1', 'type' => 'boolean'],
        ];

        foreach ($settings as $row) {
            Setting::query()->updateOrCreate(
                ['group' => $row['group'], 'key' => $row['key']],
                ['value' => $row['value'], 'type' => $row['type']]
            );
        }
    }
}
