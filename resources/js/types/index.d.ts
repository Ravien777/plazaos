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

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        client_user?: {
            id: string;
            client: {
                id: string;
                company_name: string;
            };
        };
    };
};
