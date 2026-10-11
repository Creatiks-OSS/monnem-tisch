<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'order_received',
                'name' => 'Bestellung eingegangen',
                'subject' => 'Ihre Bestellung {order_number} ist eingegangen',
                'variables' => ['name', 'order_number', 'total', 'event_date'],
                'body_html' => '<p>Hallo {name},</p><p>vielen Dank für Ihre Bestellung <strong>{order_number}</strong>.</p><p>Eventdatum: {event_date}<br>Gesamtsumme: {total} €</p><p>Wir melden uns in Kürze bei Ihnen.</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nvielen Dank für Ihre Bestellung {order_number}.\nEventdatum: {event_date}\nGesamtsumme: {total} €\n\nIhr Monnemtisch Catering Team",
            ],
            [
                'key' => 'order_confirmed',
                'name' => 'Bestellung bestätigt',
                'subject' => 'Bestätigung Ihrer Bestellung {order_number}',
                'variables' => ['name', 'order_number', 'event_date', 'total'],
                'body_html' => '<p>Hallo {name},</p><p>Ihre Bestellung <strong>{order_number}</strong> wurde bestätigt.</p><p>Wir freuen uns auf Ihr Event am {event_date}.</p><p>Gesamtsumme: {total} €</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nIhre Bestellung {order_number} wurde bestätigt.\nEventdatum: {event_date}\nGesamtsumme: {total} €\n\nIhr Monnemtisch Catering Team",
            ],
            [
                'key' => 'order_preparing',
                'name' => 'Bestellung in Vorbereitung',
                'subject' => 'Wir bereiten Ihre Bestellung {order_number} vor',
                'variables' => ['name', 'order_number', 'event_date'],
                'body_html' => '<p>Hallo {name},</p><p>gute Nachrichten: Ihre Bestellung <strong>{order_number}</strong> befindet sich in der Vorbereitung für den {event_date}.</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nIhre Bestellung {order_number} befindet sich in der Vorbereitung für den {event_date}.\n\nIhr Monnemtisch Catering Team",
            ],
            [
                'key' => 'order_on_the_way',
                'name' => 'Bestellung unterwegs',
                'subject' => 'Ihre Bestellung {order_number} ist unterwegs',
                'variables' => ['name', 'order_number'],
                'body_html' => '<p>Hallo {name},</p><p>Ihre Bestellung <strong>{order_number}</strong> ist unterwegs zu Ihnen.</p><p>Wir wünschen Ihnen ein wunderbares Event!</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nIhre Bestellung {order_number} ist unterwegs zu Ihnen.\n\nIhr Monnemtisch Catering Team",
            ],
            [
                'key' => 'order_completed',
                'name' => 'Bestellung abgeschlossen',
                'subject' => 'Vielen Dank – Bestellung {order_number} abgeschlossen',
                'variables' => ['name', 'order_number'],
                'body_html' => '<p>Hallo {name},</p><p>Ihre Bestellung <strong>{order_number}</strong> wurde erfolgreich abgeschlossen.</p><p>Wir freuen uns über Ihr Feedback und hoffentlich auf ein Wiedersehen.</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nIhre Bestellung {order_number} wurde erfolgreich abgeschlossen.\n\nIhr Monnemtisch Catering Team",
            ],
            [
                'key' => 'quote_received',
                'name' => 'Angebotseingang',
                'subject' => 'Wir haben Ihre Anfrage {quote_number} erhalten',
                'variables' => ['name', 'quote_number', 'event_date'],
                'body_html' => '<p>Hallo {name},</p><p>vielen Dank für Ihre Angebotsanfrage <strong>{quote_number}</strong>.</p><p>Wir prüfen Ihre Angaben und melden uns zeitnah bei Ihnen{event_date}.</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nvielen Dank für Ihre Angebotsanfrage {quote_number}.\nWir melden uns zeitnah bei Ihnen.\n\nIhr Monnemtisch Catering Team",
            ],
            [
                'key' => 'contact_received',
                'name' => 'Kontaktanfrage eingegangen',
                'subject' => 'Wir haben Ihre Nachricht erhalten',
                'variables' => ['name', 'subject'],
                'body_html' => '<p>Hallo {name},</p><p>vielen Dank für Ihre Nachricht{subject}.</p><p>Unser Team meldet sich so schnell wie möglich bei Ihnen.</p><p>Ihr Monnemtisch Catering Team</p>',
                'body_text' => "Hallo {name},\n\nvielen Dank für Ihre Nachricht.\nUnser Team meldet sich so schnell wie möglich bei Ihnen.\n\nIhr Monnemtisch Catering Team",
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body_html' => $template['body_html'],
                    'body_text' => $template['body_text'],
                    'variables' => $template['variables'],
                    'is_active' => true,
                ]
            );
        }
    }
}
