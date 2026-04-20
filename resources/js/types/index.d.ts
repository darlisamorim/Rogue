export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    avatar_url?: string | null;
    provider?: string | null;
    credit_balance?: string | number;
    professional_title?: string | null;
    phone?: string | null;
    location?: string | null;
    linkedin_url?: string | null;
    website_url?: string | null;
    bio?: string | null;
    date_of_birth?: string | null;   // YYYY-MM-DD
    nationality?: string | null;
    cpf?: string | null;
    is_admin?: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
