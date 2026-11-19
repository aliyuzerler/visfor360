export interface ShellUser {
    id: number;
    name: string;
    email: string;
}

/** HandleInertiaRequests ile paylaşılan kök verileri. */
export interface SharedData {
    app: {
        name: string;
        env: string;
    };
    auth: {
        user: ShellUser | null;
    };
}

export interface PageProps<T extends Record<string, unknown> = Record<string, unknown>> {
    auth: SharedData['auth'];
    app: SharedData['app'];
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    [key: string]: any;
}