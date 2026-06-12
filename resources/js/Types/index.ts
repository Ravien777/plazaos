export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    team_id: string | null;
    role: 'owner' | 'member' | null;
    avatar: string | null;
}

export interface Task {
    id: string;
    project_id: string | null;
    title: string;
    description: string | null;
    status: 'todo' | 'in_progress' | 'done';
    priority: 'low' | 'medium' | 'high';
    order: number;
    assignee_id: number | null;
    assignee: User | null;
    created_by: number;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface Lead {
    id: string;
    company_name: string;
    contact_name: string;
    email: string | null;
    phone: string | null;
    website: string | null;
    industry: string | null;
    city: string | null;
    country: string | null;
    source: string | null;
    status: string;
    notes: string | null;
    last_contacted_at: string | null;
    converted_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    activities?: Activity[];
    emails?: Email[];
}

export interface Note {
    id: string;
    noteable_type: string;
    noteable_id: string;
    content: string;
    created_by: string;
    created_at: string;
    updated_at: string;
}

export interface SearchResult {
    id: string;
    type: 'lead' | 'client' | 'project' | 'meeting' | 'ticket' | 'task';
    title: string;
    subtitle: string;
    url: string;
}

export interface SearchResults {
    leads: SearchResult[];
    clients: SearchResult[];
    projects: SearchResult[];
    meetings: SearchResult[];
    tickets: SearchResult[];
    tasks: SearchResult[];
}

export interface CommentData {
    id: string;
    body: string;
    user: {
        id: number;
        name: string;
        avatar: string | null;
    };
    created_at: string;
    can_delete: boolean;
}

export interface Activity {
    id: string;
    subject_type: string;
    subject_id: string;
    event: string;
    description: string;
    metadata: Record<string, unknown> | null;
    created_at: string;
}

export interface LeadImport {
    id: string;
    filename: string;
    status: string;
    total_rows: number;
    processed: number;
    failed: number;
    errors: string[] | null;
    created_at: string;
}

export interface ImportPreview {
    headers: string[];
    sample_rows: Record<string, string>[];
}

export interface LeadSource {
    id: string;
    name: string;
    type: string;
    config: Record<string, unknown> | null;
    is_active: boolean;
    frequency: string;
    last_run_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Client {
    id: string;
    company_name: string;
    contact_name: string;
    email: string | null;
    phone: string | null;
    website: string | null;
    industry: string | null;
    city: string | null;
    country: string | null;
    source: string | null;
    status: string;
    notes: string | null;
    lead_id: string | null;
    last_contacted_at: string | null;
    portal_token: string | null;
    portal_token_expires_at: string | null;
    maroni_client_id: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    activities?: Activity[];
    emails?: Email[];
    projects?: Project[];
    documents?: DocumentRecord[];
    tickets?: Ticket[];
    meetings?: Meeting[];
    portalUsers?: { id: string; name: string; email: string; created_at: string }[];
    intakeFormSubmissions?: IntakeFormSubmission[];
}

export interface Email {
    id: string;
    emailable_type: string;
    emailable_id: string;
    from: string;
    to: string;
    subject: string;
    body: string;
    template: string | null;
    template_data: Record<string, string> | null;
    status: string;
    external_id: string | null;
    sent_at: string | null;
    opened_at: string | null;
    created_at: string;
}

export interface EmailTemplate {
    id?: string;
    key: string;
    name: string;
    subject: string;
    body: string;
    variables: string[];
}

export interface Project {
    id: string;
    client_id: string;
    name: string;
    description: string | null;
    status: string;
    budget: string | null;
    start_date: string | null;
    due_date: string | null;
    progress_percentage: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    client?: Client;
    activities?: Activity[];
    documents?: DocumentRecord[];
    tickets?: Ticket[];
    tasks?: Task[];
}

export interface DocumentRecord {
    id: string;
    documentable_type: string;
    documentable_id: string;
    name: string;
    mime_type: string;
    size: number;
    created_at: string;
    signed_download_url: string;
}

export interface Meeting {
    id: string;
    meetable_type: string;
    meetable_id: string;
    title: string;
    description: string | null;
    start_time: string;
    end_time: string | null;
    location: string | null;
    meet_link: string | null;
    join_url: string | null;
    provider: string;
    status: string;
    user_id: number | null;
    created_at: string;
    updated_at: string;
    meetable?: Record<string, unknown>;
    user?: { id: number; name: string };
}

export interface CalendarEvent {
    id: string;
    title: string;
    start_time: string;
    end_time: string | null;
    status: string;
    user_id: number | null;
    user: { id: number; name: string } | null;
}

export interface NotificationData {
    id: string;
    type: string;
    data: {
        title: string;
        message: string;
        link: string;
        icon: string;
    };
    read_at: string | null;
    created_at: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface Ticket {
    id: string;
    ticketable_type: string | null;
    ticketable_id: string | null;
    subject: string;
    description: string | null;
    status: string;
    priority: string;
    category: string;
    user_id: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    ticketable?: Record<string, unknown>;
    replies?: TicketReply[];
    activities?: Activity[];
}

export interface TicketReply {
    id: string;
    ticket_id: string;
    user_id: string;
    body: string;
    created_at: string;
    updated_at: string;
    user?: { id: string; name: string };
}

export interface IntakeFormField {
    id: string;
    label: string;
    field_type: string;
    required: boolean;
    options: string[] | null;
    placeholder: string | null;
    sort_order: number;
}

export interface IntakeForm {
    id: string;
    title: string;
    description: string | null;
    is_active: boolean;
    fields?: IntakeFormField[];
    submissions_count?: number;
    created_at: string;
    updated_at: string;
}

export interface IntakeFormSubmissionData {
    id: string;
    intake_form_field_id: string;
    value: string | null;
    file_path: string | null;
    field: { id: string; label: string; field_type: string };
}

export interface Testimonial {
    id: string;
    client_id: string;
    project_id: string | null;
    rating: number;
    content: string | null;
    review_token: string;
    is_approved: boolean;
    submitted_at: string | null;
    created_at: string;
    updated_at: string;
    client?: { id: string; company_name: string };
    project?: { id: string; name: string };
}

export interface IntakeFormSubmission {
    id: string;
    intake_form_id: string;
    client_id: string;
    submitted_at: string;
    created_at: string;
    form?: { id: string; title: string };
    client?: { id: string; company_name: string };
    data?: IntakeFormSubmissionData[];
}

export interface Webhook {
    id: string;
    user_id: number;
    url: string;
    events: string[];
    secret: string;
    active: boolean;
    last_sent_at: string | null;
    last_error_at: string | null;
    last_error_message: string | null;
    created_at: string;
    updated_at: string;
}

export interface AllowedEvent {
    value: string;
    label: string;
}

export interface DashboardLayout {
    stat_cards: string[];
    bottom_widgets: string[];
}

export interface Plan {
    id: string;
    stripe_price_id: string | null;
    slug: string;
    name: string;
    description: string | null;
    monthly_price_cents: number;
    max_users: number | null;
    features: string[] | null;
    is_active: boolean;
    sort_order: number;
}

export interface Subscription {
    id: string;
    team_id: string;
    plan_id: string;
    stripe_subscription_id: string | null;
    stripe_customer_id: string | null;
    status: string;
    trial_ends_at: string | null;
    current_period_ends_at: string | null;
    canceled_at: string | null;
    ended_at: string | null;
    seats: number;
    plan?: Plan;
}

export interface Invoice {
    id: string;
    number: string;
    amount_due: number;
    amount_paid: number;
    status: string;
    created: number;
    pdf_url: string | null;
    paid_at: number | null;
}
