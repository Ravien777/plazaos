export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    team_id: string | null;
    role: 'owner' | 'member' | null;
    avatar: string | null;
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
