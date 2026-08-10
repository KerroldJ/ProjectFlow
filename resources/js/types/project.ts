export interface Project {
    id: number;
    client_name: string;
    project_name: string;
    description: string | null;
    status: string;
    priority: string;
    start_date: string | null;
    due_date: string | null;
    created_at: string;
    updated_at: string;
}

export interface ProjectFilters {
    search?: string;
    status?: string;
    priority?: string;
    sort?: string;
    direction?: string;
}
