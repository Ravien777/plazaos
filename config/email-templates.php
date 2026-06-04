<?php

return [
    'introduction' => [
        'name' => 'Introduction',
        'subject' => 'Introduction — {{company_name}}',
        'body' => "Hello {{contact_name}},\n\nI came across {{company_name}} and was impressed by your work in the {{industry}} industry. I believe we could help you achieve even better results.\n\nWould you be open to a quick chat this week?\n\nBest regards,\n{{sender_name}}",
        'variables' => ['company_name', 'contact_name', 'industry', 'city', 'website', 'sender_name'],
    ],
    'follow_up' => [
        'name' => 'Follow Up',
        'subject' => 'Following up — {{company_name}}',
        'body' => "Hi {{contact_name}},\n\nJust following up on my previous message. I'd love to discuss how we might work together.\n\nLet me know if you have any questions.\n\nBest regards,\n{{sender_name}}",
        'variables' => ['company_name', 'contact_name', 'industry', 'city', 'website', 'sender_name'],
    ],
    'proposal' => [
        'name' => 'Proposal',
        'subject' => 'Proposal for {{company_name}}',
        'body' => "Hi {{contact_name}},\n\nHere is the proposal we discussed for {{company_name}}. I'm confident this would be a great fit.\n\nPlease review and let me know your thoughts.\n\nBest regards,\n{{sender_name}}",
        'variables' => ['company_name', 'contact_name', 'industry', 'city', 'website', 'sender_name'],
    ],
    'meeting_request' => [
        'name' => 'Meeting Request',
        'subject' => 'Meeting Request — {{company_name}}',
        'body' => "Hi {{contact_name}},\n\nWould you be available for a 30-minute call this week? I'd love to learn more about {{company_name}}'s goals and see how we can help.\n\nPlease let me know what works for you.\n\nBest regards,\n{{sender_name}}",
        'variables' => ['company_name', 'contact_name', 'industry', 'city', 'website', 'sender_name'],
    ],
    'thank_you' => [
        'name' => 'Thank You',
        'subject' => 'Thank you — {{company_name}}',
        'body' => "Hi {{contact_name}},\n\nThank you for your time and the insightful conversation about {{company_name}}. I look forward to exploring how we can work together.\n\nBest regards,\n{{sender_name}}",
        'variables' => ['company_name', 'contact_name', 'industry', 'city', 'website', 'sender_name'],
    ],
    'custom' => [
        'name' => 'Custom',
        'subject' => '',
        'body' => '',
        'variables' => ['company_name', 'contact_name', 'industry', 'city', 'website', 'sender_name'],
    ],
];
