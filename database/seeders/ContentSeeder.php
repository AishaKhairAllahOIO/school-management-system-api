<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            'global.brand_name' => 'Madrasaty Academy',

            'global.navbar.links.home' => 'Home',
            'global.navbar.links.about' => 'About Us',
            'global.navbar.links.activities_events' => 'Activities & Events',
            'global.navbar.links.support_contact' => 'Support & Contact',

            'global.navbar.aria_labels.toggle_theme' => 'Toggle Theme',
            'global.navbar.aria_labels.toggle_menu' => 'Toggle Menu',

            'global.footer.description' =>
                "Empowering tomorrow's leaders through innovation, excellence, and a holistic education experience.",

            'global.footer.sections.school' => 'School',
            'global.footer.sections.support_contact' => 'Support & Contact',

            'global.footer.copyright' =>
                '© 2026 Madrasaty Academy. All rights reserved.',


            // -------------------------------------------------
            // HOME PAGE
            // -------------------------------------------------

            'home_page.hero.badge' =>
                '#1 Ranked School in the Region 2026',

            'home_page.hero.title_part_1' =>
                'Empowering',

            'home_page.hero.title_highlight' =>
                "Tomorrow's",

            'home_page.hero.title_part_2' =>
                'Leaders Today',

            'home_page.hero.description' =>
                'Madrasaty Academy combines world-class Cambridge education with cutting-edge technology to deliver a transformative learning experience for students from seventh to Grade 12.',

            'home_page.hero.buttons.contact' =>
                'Contact Us',

            'home_page.hero.buttons.discover' =>
                'Discover More',


            'home_page.features_section.badge' =>
                'Powered by Smart ERP',

            'home_page.features_section.title' =>
                'Everything Your School Needs, In One Platform',

            'home_page.features_section.description' =>
                'Our integrated School ERP ecosystem brings together every aspect of school management into a single, intuitive platform.',


            'home_page.why_us_section.badge' =>
                'Why Madrasaty Academy',

            'home_page.why_us_section.title' =>
                'The Future of Education is Already Here',

            'home_page.why_us_section.description' =>
                "We don't just teach — we prepare students for the real world through a blend of rigorous academics, technology, and character development.",

            'home_page.why_us_section.cambridge_badge.title' =>
                'Cambridge Certified',

            'home_page.why_us_section.cambridge_badge.subtitle' =>
                'Since 1998 · KG to Grade 12',


            'home_page.achievements_section.title' =>
                'By the Numbers',

            'home_page.achievements_section.description' =>
                'Our achievements speak for themselves.',


            'home_page.testimonials_section.badge' =>
                'Real Stories',

            'home_page.testimonials_section.title' =>
                'What Our Community Says',

            'home_page.testimonials_section.description' =>
                'Hear from students, parents, and teachers about their Madrasaty Academy experience.',


            // -------------------------------------------------
            // ABOUT PAGE
            // -------------------------------------------------

            'about_page.hero.badge' =>
                'Our Story',

            'about_page.hero.title' =>
                'Building a Legacy of Academic Excellence',

            'about_page.hero.description' =>
                "Since 1998, Madrasaty Academy has been shaping the minds of tomorrow's leaders. We combine rigorous Cambridge academics with cutting-edge technology and a nurturing environment to help every student reach their full potential.",


            'about_page.mission_vision.mission.title' =>
                'Our Mission',

            'about_page.mission_vision.vision.title' =>
                'Our Vision',

            'about_page.mission_vision.promise.title' =>
                'Our Promise',


            'about_page.core_values.title' =>
                'Core Values',

            'about_page.core_values.description' =>
                'These principles guide every decision we make and every interaction we have.',


            'about_page.principal_message.badge' =>
                "Principal's Message",

            'about_page.principal_message.title' =>
                'A Message from Our Principal',


            'about_page.timeline_section.title' =>
                'Our Journey Through the Years',

            'about_page.timeline_section.description' =>
                'From a small campus with big dreams to a regional leader in education.',


            // -------------------------------------------------
            // ACTIVITIES & EVENTS PAGE
            // -------------------------------------------------

            'activities_events_page.hero.badge' =>
                'Campus Life',

            'activities_events_page.hero.title' =>
                'Explore Our Vibrant Community',

            'activities_events_page.hero.description' =>
                'Discover opportunities beyond the classroom. Join our diverse clubs, stay updated with the academic calendar, and participate in upcoming school events.',


            'activities_events_page.hero.tabs.activities' =>
                'Activities & Clubs',

            'activities_events_page.hero.tabs.events' =>
                'Events Calendar',


            'activities_events_page.activities_tab.members_label' =>
                'members',

            'activities_events_page.activities_tab.open_to_label' =>
                'Open to:',

            'activities_events_page.activities_tab.buttons.show_less' =>
                'Show less',

            'activities_events_page.activities_tab.buttons.learn_more' =>
                'Learn more',


            'activities_events_page.events_tab.filter_label' =>
                'Filter by type',

            'activities_events_page.events_tab.clear_date' =>
                'Clear date',

            'activities_events_page.events_tab.all_upcoming' =>
                'All Upcoming Events',

            'activities_events_page.events_tab.events_count_label' =>
                'events',

            'activities_events_page.events_tab.no_events' =>
                'No events found for the selected filters.',

            'activities_events_page.events_tab.multi_day_badge' =>
                'Multi-day event',


            // -------------------------------------------------
            // SUPPORT & CONTACT PAGE
            // -------------------------------------------------

            'support_contact_page.hero.badge' =>
                'Help & Communications Hub',

            'support_contact_page.hero.title' =>
                'How Can We Assist You Today?',

            'support_contact_page.hero.description' =>
                'Get in touch with our administrative departments or browse our support center. We are always here to help.',


            'support_contact_page.hero.tabs.contact' =>
                'Contact & Departments',

            'support_contact_page.hero.tabs.support' =>
                'Support Center & FAQs',


            'support_contact_page.contact_tab.form.title' =>
                'Send Us a Direct Message',

            'support_contact_page.contact_tab.form.description' =>
                'Fill out the form below and the relevant department will respond within one business day.',


            'support_contact_page.contact_tab.form.labels.name' =>
                'Full Name *',

            'support_contact_page.contact_tab.form.labels.email' =>
                'Email Address *',

            'support_contact_page.contact_tab.form.labels.phone' =>
                'Phone Number',

            'support_contact_page.contact_tab.form.labels.department' =>
                'Department',

            'support_contact_page.contact_tab.form.labels.subject' =>
                'Subject *',

            'support_contact_page.contact_tab.form.labels.message' =>
                'Message *',


            'support_contact_page.contact_tab.form.placeholders.subject' =>
                'What is your enquiry about?',

            'support_contact_page.contact_tab.form.placeholders.message' =>
                'Please provide details...',


            'support_contact_page.contact_tab.form.submit_btn' =>
                'Send Message',

            'support_contact_page.contact_tab.form.submitting_btn' =>
                'Sending...',


            'support_contact_page.contact_tab.form.success.title' =>
                'Message Sent!',

            'support_contact_page.contact_tab.form.success.message' =>
                "We've received your message and will reply shortly.",

            'support_contact_page.contact_tab.form.success.button' =>
                'Send another message',


            'support_contact_page.contact_tab.directory.title' =>
                'Department Directory',

            'support_contact_page.contact_tab.office_hours.title' =>
                'Office Hours',


            'support_contact_page.support_tab.categories.title' =>
                'Support Categories',

            'support_contact_page.support_tab.categories.description' =>
                'Explore our main help categories for quick answers.',

            'support_contact_page.support_tab.categories.topics_label' =>
                'topics',


            'support_contact_page.support_tab.faqs.title' =>
                'Frequently Asked Questions',


            'support_contact_page.map_section.title' =>
                'Madrasaty Academy',

            'support_contact_page.map_section.subtitle' =>
                'Academic City, Syria',

            'support_contact_page.map_section.button' =>
                'Open in Google Maps',
        ];

        foreach ($contents as $key => $value) {
            Content::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}