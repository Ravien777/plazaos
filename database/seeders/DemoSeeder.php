<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IntakeFieldType;
use App\Enums\LeadStatus;
use App\Enums\MeetingStatus;
use App\Enums\ProjectStatus;
use App\Enums\SourceType;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Activity;
use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Document;
use App\Models\Email;
use App\Models\IntakeForm;
use App\Models\IntakeFormField;
use App\Models\IntakeFormSubmission;
use App\Models\IntakeFormSubmissionData;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Meeting;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\Testimonial;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $team = Team::create([
            'name' => 'Demo Team',
            'owner_id' => $admin->id,
        ]);

        $admin->update([
            'team_id' => $team->id,
            'role' => 'owner',
        ]);

        $this->createLeadSources();
        $leads = $this->createLeads();
        $clients = $this->createClients($leads);
        $portalUsers = $this->createPortalUsers($clients);
        $projects = $this->createProjects($clients);
        $this->createTasks($projects, $admin);
        $formFieldMap = $this->createIntakeForms();
        $this->createIntakeSubmissions($clients, $portalUsers, $formFieldMap);
        $this->createMeetings($clients, $leads, $projects, $admin);
        $this->createNotes($leads, $clients, $projects, $admin);
        $tickets = $this->createTickets($clients, $projects, $admin);
        $this->createTicketReplies($tickets, $admin);
        $this->createNotesOnTickets($tickets, $admin);
        $this->createEmails($leads, $clients, $projects);
        $this->createDocuments($leads, $clients, $projects, $tickets, $admin);
        $this->createActivities($leads, $clients, $projects, $tickets);
        $this->createTestimonials($clients, $projects);
    }

    private function createLeadSources(): void
    {
        LeadSource::create([
            'name' => 'LinkedIn Outreach',
            'type' => SourceType::LinkedIn,
            'config' => ['keywords' => 'web developer', 'location' => 'remote'],
            'is_active' => true,
        ]);

        LeadSource::create([
            'name' => 'Upwork Feed',
            'type' => SourceType::Upwork,
            'config' => ['skills' => ['laravel', 'vue', 'react']],
            'is_active' => true,
        ]);

        LeadSource::create([
            'name' => 'Cold Email List',
            'type' => SourceType::ColdEmail,
            'config' => ['source' => 'apollo.io'],
            'is_active' => false,
        ]);
    }

    /**
     * @return Lead[]
     */
    private function createLeads(): array
    {
        $leadData = [
            ['Acme Corp', 'John Smith', 'john@acme.com', LeadStatus::New],
            ['Globex Inc', 'Jane Doe', 'jane@globex.com', LeadStatus::Interested],
            ['Initech', 'Bill Lumbergh', 'bill@initech.com', LeadStatus::ProposalSent],
            ['Cyberdyne Systems', 'Miles Dyson', 'miles@cyberdyne.com', LeadStatus::Qualified],
            ['Wonka Industries', 'Willy Wonka', 'willy@wonka.com', LeadStatus::Contacted],
            ['Stark Industries', 'Tony Stark', 'tony@stark.com', LeadStatus::MeetingScheduled],
            ['Wayne Enterprises', 'Bruce Wayne', 'bruce@wayne.com', LeadStatus::Won],
            ['Oscorp', 'Norman Osborn', 'norman@oscorp.com', LeadStatus::Lost],
            ['Massive Dynamic', 'Walter Bishop', 'walter@massive.com', LeadStatus::New],
            ['Umbrella Corp', 'Albert Wesker', 'albert@umbrella.com', LeadStatus::Archived],
            ['Nakatomi Trading', 'Hans Gruber', 'hans@nakatomi.com', LeadStatus::New],
            ['Tyrell Corp', 'Eldon Tyrell', 'eldon@tyrell.com', LeadStatus::Qualified],
            ['Weyland-Yutani', 'Carter Burke', 'carter@weyland.com', LeadStatus::Interested],
            ['Soylent Corp', 'Charles Lee', 'charles@soylent.com', LeadStatus::Contacted],
            ['Aperture Science', 'Cave Johnson', 'cave@aperture.com', LeadStatus::ProposalSent],
            ['Black Mesa', 'Gordon Freeman', 'gordon@blackmesa.com', LeadStatus::New],
            ['LexCorp', 'Lex Luthor', 'lex@lexcorp.com', LeadStatus::MeetingScheduled],
            ['Omni Consumer Products', 'Dick Jones', 'dick@ocp.com', LeadStatus::Qualified],
            ['Delos Inc', 'Robert Ford', 'robert@delos.com', LeadStatus::New],
            ['Vandelay Industries', 'Art Vandelay', 'art@vandelay.com', LeadStatus::New],
        ];

        $leads = [];
        foreach ($leadData as [$company, $contact, $email, $status]) {
            $leads[] = Lead::factory()->create([
                'company_name' => $company,
                'contact_name' => $contact,
                'email' => $email,
                'status' => $status,
                'converted_at' => $status === LeadStatus::Won ? now() : null,
            ]);
        }

        return $leads;
    }

    /**
     * @param Lead[] $leads
     * @return Client[]
     */
    private function createClients(array $leads): array
    {
        $wonLeads = array_filter($leads, fn (Lead $l) => $l->status === LeadStatus::Won);
        $converted = array_slice(array_values($wonLeads), 0, 3);

        $clients = [];
        foreach ($converted as $lead) {
            $clients[] = Client::factory()->create([
                'company_name' => $lead->company_name,
                'contact_name' => $lead->contact_name,
                'email' => $lead->email,
                'lead_id' => $lead->id,
            ]);
        }

        $directClients = [
            ['Acme Corp', 'John Smith', 'john@acme-corp.com'],
            ['Globex Inc', 'Jane Doe', 'jane@globex.com'],
            ['Initech', 'Bill Lumbergh', 'bill@initech.com'],
            ['Stark Industries', 'Tony Stark', 'tony@starkindustries.com'],
            ['Wayne Enterprises', 'Bruce Wayne', 'bruce@wayneenterprises.com'],
        ];

        foreach ($directClients as [$company, $contact, $email]) {
            $clients[] = Client::factory()->create([
                'company_name' => $company,
                'contact_name' => $contact,
                'email' => $email,
            ]);
        }

        return $clients;
    }

    /**
     * @param Client[] $clients
     * @return ClientUser[]
     */
    private function createPortalUsers(array $clients): array
    {
        $portalUsers = [];
        $data = [
            [$clients[0], 'Sarah Connor', 'sarah@acme-corp.com'],
            [$clients[1], 'Milton Waddams', 'milton@globex.com'],
            [$clients[2], 'Samir Nagheenanajar', 'samir@initech.com'],
            [$clients[3], 'Pepper Potts', 'pepper@starkindustries.com'],
        ];

        foreach ($data as [$client, $name, $email]) {
            $portalUsers[] = ClientUser::factory()->create([
                'client_id' => $client->id,
                'name' => $name,
                'email' => $email,
            ]);
        }

        return $portalUsers;
    }

    /**
     * @param Client[] $clients
     * @return Project[]
     */
    private function createProjects(array $clients): array
    {
        $projectNames = [
            'Website Redesign',
            'Mobile App Development',
            'API Integration',
            'E-commerce Platform',
            'CRM Implementation',
            'Data Migration',
            'Brand Identity Refresh',
            'Cloud Infrastructure Setup',
            'Security Audit',
            'Marketing Automation',
            'Customer Portal Build',
            'Analytics Dashboard',
        ];

        $statuses = ProjectStatus::cases();
        $projects = [];
        foreach ($projectNames as $i => $name) {
            $client = $clients[$i % count($clients)];
            $projects[] = Project::factory()->create([
                'client_id' => $client->id,
                'name' => $name,
                'status' => $statuses[$i % count($statuses)],
            ]);
        }

        return $projects;
    }

    /**
     * @param Project[] $projects
     */
    private function createTasks(array $projects, User $admin): void
    {
        $taskData = [
            ['Homepage redesign', $projects[0], 'todo', 'high', 0],
            ['Create wireframes', $projects[0], 'in_progress', 'medium', 0],
            ['Design system setup', $projects[0], 'done', 'low', 0],
            ['User authentication', $projects[1], 'todo', 'high', 0],
            ['Push notifications', $projects[1], 'in_progress', 'medium', 0],
            ['API endpoint docs', $projects[2], 'todo', 'low', 0],
            ['Payment gateway', $projects[3], 'todo', 'high', 0],
            ['Shopping cart UI', $projects[3], 'in_progress', 'medium', 0],
            ['Order management', $projects[3], 'done', 'medium', 0],
            ['Contact import', $projects[4], 'todo', 'medium', 0],
            ['Email templates', $projects[4], 'in_progress', 'low', 0],
            ['Data mapping', $projects[5], 'todo', 'high', 0],
        ];

        foreach ($taskData as [$title, $project, $status, $priority, $order]) {
            Task::create([
                'project_id' => $project->id,
                'title' => $title,
                'status' => $status,
                'priority' => $priority,
                'order' => $order,
                'created_by' => $admin->id,
                'assignee_id' => $admin->id,
            ]);
        }
    }

    /**
     * @return array{IntakeFormField[]}
     */
    private function createIntakeForms(): array
    {
        $form1 = IntakeForm::create([
            'title' => 'New Client Onboarding',
            'description' => 'Collect initial information from new clients before project kickoff.',
            'is_active' => true,
        ]);

        $form1FieldData = [
            ['Company Name', IntakeFieldType::Text, true, null, 'Enter your company name'],
            ['Contact Person', IntakeFieldType::Text, true, null, 'Full name of the primary contact'],
            ['Email Address', IntakeFieldType::Email, true, null, 'company@example.com'],
            ['Company Size', IntakeFieldType::Select, true, ['1-10', '11-50', '51-200', '201-1000', '1000+'], null],
            ['Services Needed', IntakeFieldType::MultiSelect, true, ['Web Development', 'Mobile Apps', 'Design', 'Consulting', 'DevOps', 'AI/ML'], null],
            ['Project Timeline', IntakeFieldType::Select, true, ['ASAP', '1-3 months', '3-6 months', '6+ months', 'Undecided'], null],
            ['Additional Notes', IntakeFieldType::Textarea, false, null, 'Any other information you would like to share'],
            ['Brand Guidelines', IntakeFieldType::File, false, null, 'Upload your brand assets (optional)'],
            ['Start Date', IntakeFieldType::Date, false, null, null],
        ];

        $form1Fields = [];
        foreach ($form1FieldData as $i => [$label, $type, $required, $options, $placeholder]) {
            $form1Fields[] = IntakeFormField::create([
                'intake_form_id' => $form1->id,
                'label' => $label,
                'field_type' => $type,
                'required' => $required,
                'options' => $options,
                'placeholder' => $placeholder,
                'sort_order' => $i + 1,
            ]);
        }

        $form2 = IntakeForm::create([
            'title' => 'Project Kickoff Questionnaire',
            'description' => 'Gather technical requirements and preferences before starting a new project.',
            'is_active' => true,
        ]);

        $form2FieldData = [
            ['What is your tech stack preference?', IntakeFieldType::MultiSelect, true, ['Laravel', 'Vue.js', 'React', 'Node.js', 'Python', 'Go', 'AWS', 'Azure', 'Other'], null],
            ['Do you have existing code?', IntakeFieldType::Select, true, ['Yes, on GitHub', 'Yes, on GitLab', 'Yes, on Bitbucket', 'No, starting fresh'], null],
            ['Repository URL', IntakeFieldType::Text, false, null, 'https://github.com/your-org/repo'],
            ['Database preference', IntakeFieldType::Select, false, ['PostgreSQL', 'MySQL', 'MongoDB', 'SQLite', 'Not Sure'], null],
            ['Deployment target', IntakeFieldType::Select, true, ['AWS', 'DigitalOcean', 'Vercel', 'Netlify', 'Self-hosted', 'Not Sure'], null],
            ['Any specific deadlines?', IntakeFieldType::Date, false, null, null],
            ['Additional requirements', IntakeFieldType::Textarea, false, null, 'Describe any specific requirements or constraints'],
        ];

        $form2Fields = [];
        foreach ($form2FieldData as $i => [$label, $type, $required, $options, $placeholder]) {
            $form2Fields[] = IntakeFormField::create([
                'intake_form_id' => $form2->id,
                'label' => $label,
                'field_type' => $type,
                'required' => $required,
                'options' => $options,
                'placeholder' => $placeholder,
                'sort_order' => $i + 1,
            ]);
        }

        return ['form1Fields' => $form1Fields, 'form2Fields' => $form2Fields];
    }

    /**
     * @param Client[] $clients
     * @param ClientUser[] $portalUsers
     * @param array $formFieldMap
     */
    private function createIntakeSubmissions(array $clients, array $portalUsers, array $formFieldMap): void
    {
        $form1Fields = $formFieldMap['form1Fields'];
        $form2Fields = $formFieldMap['form2Fields'];

        $form1 = $form1Fields[0]->form;

        $sub1 = IntakeFormSubmission::create([
            'intake_form_id' => $form1->id,
            'client_id' => $clients[0]->id,
            'client_user_id' => $portalUsers[0]->id,
            'submitted_at' => now()->subDays(5),
        ]);

        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub1->id, 'intake_form_field_id' => $form1Fields[0]->id, 'value' => 'Acme Corp']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub1->id, 'intake_form_field_id' => $form1Fields[1]->id, 'value' => 'Sarah Connor']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub1->id, 'intake_form_field_id' => $form1Fields[2]->id, 'value' => 'sarah@acme-corp.com']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub1->id, 'intake_form_field_id' => $form1Fields[3]->id, 'value' => '51-200']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub1->id, 'intake_form_field_id' => $form1Fields[4]->id, 'value' => json_encode(['Web Development', 'DevOps'])]);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub1->id, 'intake_form_field_id' => $form1Fields[5]->id, 'value' => '1-3 months']);

        $sub2 = IntakeFormSubmission::create([
            'intake_form_id' => $form1->id,
            'client_id' => $clients[3]->id,
            'client_user_id' => $portalUsers[3]->id,
            'submitted_at' => now()->subDays(2),
        ]);

        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub2->id, 'intake_form_field_id' => $form1Fields[0]->id, 'value' => 'Stark Industries']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub2->id, 'intake_form_field_id' => $form1Fields[1]->id, 'value' => 'Tony Stark']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub2->id, 'intake_form_field_id' => $form1Fields[4]->id, 'value' => json_encode(['AI/ML', 'Mobile Apps', 'Web Development'])]);

        $form2 = $form2Fields[0]->form;

        $sub3 = IntakeFormSubmission::create([
            'intake_form_id' => $form2->id,
            'client_id' => $clients[2]->id,
            'client_user_id' => null,
            'submitted_at' => now()->subDay(),
        ]);

        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub3->id, 'intake_form_field_id' => $form2Fields[0]->id, 'value' => json_encode(['Laravel', 'Vue.js', 'AWS'])]);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub3->id, 'intake_form_field_id' => $form2Fields[1]->id, 'value' => 'Yes, on GitHub']);
        IntakeFormSubmissionData::create(['intake_form_submission_id' => $sub3->id, 'intake_form_field_id' => $form2Fields[3]->id, 'value' => 'PostgreSQL']);
    }

    /**
     * @param Client[] $clients
     * @param Lead[] $leads
     * @param Project[] $projects
     */
    private function createMeetings(array $clients, array $leads, array $projects, User $admin): void
    {
        $meetingData = [
            ['Discovery Call: Acme Corp', $clients[0]],
            ['Project Kickoff: Globex', $clients[1]],
            ['Sprint Review: Initech', $clients[2]],
            ['Strategy Session: Stark Industries', $clients[3]],
            ['Budget Review: Wayne Enterprises', $clients[4]],
            ['Lead Follow-up: Cyberdyne', $leads[3]],
            ['Proposal Meeting: Wonka Industries', $leads[4]],
            ['Demo Session: LexCorp', $leads[16]],
            ['Website Redesign Review', $projects[0]],
            ['Mobile App Milestone', $projects[1]],
        ];

        $startDay = 3;
        foreach ($meetingData as $i => [$title, $meetable]) {
            $startTime = now()->addDays($startDay + $i * 2);
            Meeting::factory()->create([
                'meetable_type' => $meetable->getMorphClass(),
                'meetable_id' => $meetable->id,
                'title' => $title,
                'start_time' => $startTime,
                'end_time' => (clone $startTime)->addHours(1),
                'status' => MeetingStatus::Scheduled,
                'user_id' => $admin->id,
            ]);
        }
    }

    /**
     * @param Lead[] $leads
     * @param Client[] $clients
     * @param Project[] $projects
     */
    private function createNotes(array $leads, array $clients, array $projects, User $admin): void
    {
        $noteContents = [
            'Followed up via email, waiting for response.',
            'Requested a demo of the product.',
            'Very interested in our services.',
            'Budget seems tight, might need to adjust proposal.',
            'Initial meeting went well, they want to move forward.',
            'Client requested additional features for the project.',
            'Project is ahead of schedule.',
            'Client approved the design mockups.',
            'Lead is not ready to buy yet, follow up next quarter.',
            'Need to schedule follow-up meeting.',
            'Resolved ticket about login issues.',
        ];

        $targets = [
            [$leads[0]],
            [$leads[1]],
            [$leads[5]],
            [$leads[7]],
            [$clients[0]],
            [$clients[1]],
            [$clients[3]],
            [$clients[4]],
            [$projects[0]],
            [$projects[2]],
            [$projects[5]],
        ];

        foreach ($targets as $i => [$target]) {
            Note::create([
                'noteable_type' => $target->getMorphClass(),
                'noteable_id' => $target->id,
                'content' => $noteContents[$i],
                'created_by' => $admin->id,
            ]);
        }
    }

    /**
     * @param Client[] $clients
     * @param Project[] $projects
     * @return Ticket[]
     */
    private function createTickets(array $clients, array $projects, User $admin): array
    {
        $ticketData = [
            ['Login page not loading', 'Client reports the login page returns a 500 error on production.', $clients[0], TicketStatus::Open, TicketPriority::High, TicketCategory::BugReport],
            ['Add dark mode support', 'Would like to have a dark mode toggle on the dashboard.', $clients[1], TicketStatus::InProgress, TicketPriority::Medium, TicketCategory::FeatureRequest],
            ['Invoice generation failing', 'Invoice PDF generation fails for orders over $10,000.', $projects[2], TicketStatus::WaitingClient, TicketPriority::High, TicketCategory::Support],
            ['Database optimization', 'Query performance degradation on reports page.', $projects[4], TicketStatus::InProgress, TicketPriority::Medium, TicketCategory::Support],
            ['API rate limiting', 'Implement rate limiting on public API endpoints.', $clients[3], TicketStatus::Closed, TicketPriority::Low, TicketCategory::FeatureRequest],
            ['SSL certificate expiry', 'SSL certificate expiring in 2 weeks, needs renewal.', $projects[6], TicketStatus::Open, TicketPriority::High, TicketCategory::Support],
        ];

        $tickets = [];
        foreach ($ticketData as [$subject, $description, $ticketable, $status, $priority, $category]) {
            $tickets[] = Ticket::create([
                'ticketable_type' => $ticketable->getMorphClass(),
                'ticketable_id' => $ticketable->id,
                'subject' => $subject,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'category' => $category,
                'user_id' => $admin->id,
            ]);
        }

        return $tickets;
    }

    /**
     * @param Ticket[] $tickets
     */
    private function createTicketReplies(array $tickets, User $admin): void
    {
        $replyBodies = [
            'I am looking into this issue right now.',
            'Found the root cause. It was a missing route definition.',
            'Dark mode implementation is in progress. Targeting completion by end of sprint.',
            'Here is a preview of the dark mode design.',
            'Can you provide more details about the failing invoices?',
            'We need the exact order IDs that failed.',
            'The slow query has been identified and indexed. Performance improved by 80%.',
            'Deployed the fix to staging for verification.',
            'Rate limiting has been implemented on all public endpoints.',
            'SSL certificate has been renewed and deployed.',
        ];

        $replyIndex = 0;
        foreach ($tickets as $ticket) {
            if ($ticket->status === TicketStatus::Closed) {
                continue;
            }

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $admin->id,
                'body' => $replyBodies[$replyIndex++ % count($replyBodies)],
            ]);

            if ($ticket->status !== TicketStatus::WaitingClient) {
                TicketReply::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'body' => $replyBodies[$replyIndex++ % count($replyBodies)],
                ]);
            }
        }
    }

    /**
     * @param Ticket[] $tickets
     */
    private function createNotesOnTickets(array $tickets, User $admin): void
    {
        Note::create([
            'noteable_type' => $tickets[0]->getMorphClass(),
            'noteable_id' => $tickets[0]->id,
            'content' => 'Investigated the issue. It appears to be a PHP memory limit problem. Increased limit to 256M temporarily.',
            'created_by' => $admin->id,
        ]);
    }

    /**
     * @param Lead[] $leads
     * @param Client[] $clients
     * @param Project[] $projects
     */
    private function createEmails(array $leads, array $clients, array $projects): void
    {
        $emailData = [
            [$leads[0], 'john@example.com', 'Introduction to PlazaOS', 'Hi John, thanks for your interest in PlazaOS.'],
            [$leads[1], 'jane@example.com', 'Proposal Follow-up', 'Hi Jane, I wanted to follow up on the proposal.'],
            [$leads[5], 'tony@example.com', 'Meeting Confirmation', 'Hi Tony, confirmed our meeting for next Tuesday.'],
            [$leads[16], 'lex@example.com', 'Demo Invitation', 'Hi Lex, we would like to invite you to a demo.'],
            [$clients[0], 'sarah@example.com', 'Project Kickoff Agenda', 'Hi Sarah, here is the agenda for our kickoff meeting.'],
            [$clients[1], 'milton@example.com', 'Invoice Attached', 'Dear Milton, please find attached the invoice.'],
            [$clients[2], 'bill@example.com', 'Sprint Update', 'Hi Bill, here is the latest sprint update.'],
            [$clients[3], 'pepper@example.com', 'Quarterly Review', 'Hi Pepper, let us schedule our quarterly review.'],
            [$projects[0], 'sarah@example.com', 'Website Design Approval', 'Hi Sarah, the website mockups have been finalized.'],
            [$projects[4], 'security@example.com', 'Security Audit Report', 'The security audit has been completed.'],
        ];

        foreach ($emailData as [$emailable, $to, $subject, $body]) {
            Email::create([
                'emailable_type' => $emailable->getMorphClass(),
                'emailable_id' => $emailable->id,
                'from' => 'test@example.com',
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
                'status' => 'sent',
                'sent_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }

    /**
     * @param Lead[] $leads
     * @param Client[] $clients
     * @param Project[] $projects
     * @param Ticket[] $tickets
     */
    private function createDocuments(array $leads, array $clients, array $projects, array $tickets, User $admin): void
    {
        $docNames = [
            ['Proposal.pdf', 'application/pdf', 204800],
            ['Contract_v2.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 153600],
            ['Logo_Final.png', 'image/png', 512000],
            ['Brand_Guidelines.pdf', 'application/pdf', 1024000],
            ['Requirements_Spec.pdf', 'application/pdf', 819200],
            ['Invoice_2025_03.pdf', 'application/pdf', 102400],
            ['Screenshot_Bug.png', 'image/png', 256000],
            ['API_Docs.html', 'text/html', 409600],
            ['Database_Schema.sql', 'text/plain', 51200],
            ['Meeting_Notes.pdf', 'application/pdf', 204800],
            ['Architecture_Diagram.png', 'image/png', 1048576],
            ['Sprint_Report.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 307200],
            ['Deployment_Guide.pdf', 'application/pdf', 614400],
            ['Test_Results.xml', 'application/xml', 409600],
            ['Budget_v3.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 204800],
        ];

        $docTargets = [
            $leads[0], $leads[2], $clients[0], $clients[1], $clients[2],
            $clients[3], $tickets[0], $projects[0], $projects[1], $projects[2],
            $projects[3], $clients[4], $projects[4], $tickets[2], $projects[5],
        ];

        foreach ($docNames as $i => [$name, $mime, $size]) {
            Document::create([
                'documentable_type' => $docTargets[$i]->getMorphClass(),
                'documentable_id' => $docTargets[$i]->id,
                'name' => $name,
                'path' => 'demo/' . strtolower(str_replace(' ', '_', $name)),
                'mime_type' => $mime,
                'size' => $size,
                'user_id' => $admin->id,
            ]);
        }
    }

    /**
     * @param Lead[] $leads
     * @param Client[] $clients
     * @param Project[] $projects
     * @param Ticket[] $tickets
     */
    private function createTestimonials(array $clients, array $projects): void
    {
        $data = [
            [$clients[0], $projects[0], 5, 'Exceptional service! They went above and beyond our expectations. Highly recommend.'],
            [$clients[2], $projects[2], 5, 'Professional team that delivered exactly what we needed. Great communication throughout.'],
            [$clients[4], null, 5, 'Transformed our online presence. Our sales have increased 40% since launch.'],
            [$clients[6], null, 4, 'Very happy with the results. The team was responsive and addressed all our concerns.'],
        ];

        foreach ($data as [$client, $project, $rating, $content]) {
            Testimonial::create([
                'client_id' => $client->id,
                'project_id' => $project?->id,
                'rating' => $rating,
                'content' => $content,
                'review_token' => (string) Str::uuid(),
                'is_approved' => true,
                'submitted_at' => now()->subDays(fake()->numberBetween(1, 60)),
            ]);
        }
    }

    private function createActivities(array $leads, array $clients, array $projects, array $tickets): void
    {
        $activityData = [
            [$leads[0], 'lead_created', 'Lead created'],
            [$leads[0], 'lead_status_changed', 'Lead status changed to New'],
            [$leads[1], 'lead_created', 'Lead created'],
            [$leads[5], 'meeting_scheduled', 'Meeting scheduled with lead'],
            [$leads[7], 'lead_status_changed', 'Lead status changed to Lost'],
            [$clients[0], 'client_created', 'Client created from lead conversion'],
            [$clients[0], 'note_added', 'Note added to client'],
            [$clients[2], 'email_sent', 'Email sent to client'],
            [$clients[3], 'document_uploaded', 'Document uploaded for client'],
            [$projects[0], 'project_created', 'Project created'],
            [$projects[0], 'project_status_changed', 'Project status changed to Design'],
            [$projects[2], 'project_created', 'Project created'],
            [$tickets[0], 'ticket_created', 'Ticket created - Login page not loading'],
            [$tickets[1], 'ticket_created', 'Ticket created - Add dark mode support'],
            [$tickets[1], 'ticket_status_changed', 'Ticket status changed to In Progress'],
        ];

        foreach ($activityData as [$subject, $event, $description]) {
            Activity::create([
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->id,
                'event' => $event,
                'description' => $description,
            ]);
        }
    }
}
