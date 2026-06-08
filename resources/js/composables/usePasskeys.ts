import axios from 'axios';
import { startRegistration, startAuthentication } from '@simplewebauthn/browser';
import { useToast } from '@/composables/useToast';

export interface Passkey {
    id: number;
    name: string;
    authenticator: string | null;
    created_at: string | null;
    last_used_at: string | null;
}

export function usePasskeys() {
    const toast = useToast();

    async function registerPasskey(name: string): Promise<void> {
        try {
            const { data } = await axios.get(route('passkey.registration-options'));
            const credential = await startRegistration({ optionsJSON: data.options });
            await axios.post(route('passkey.store'), {
                name,
                credential,
            });
            toast.success('Passkey registered successfully.');
        } catch (error: any) {
            if (error?.name === 'SecurityError' || error?.message?.includes('not supported')) {
                toast.error('Passkeys are not supported by your browser or device.');
            } else if (error?.response?.status === 419) {
                toast.error('Session expired. Please refresh and try again.');
            } else {
                toast.error(error?.response?.data?.message ?? 'Failed to register passkey.');
            }
            throw error;
        }
    }

    async function deletePasskey(passkeyId: number): Promise<void> {
        try {
            await axios.delete(route('passkey.destroy', { passkey: passkeyId }));
            toast.success('Passkey deleted.');
        } catch {
            toast.error('Failed to delete passkey.');
            throw new Error('Failed to delete passkey');
        }
    }

    async function authenticate(): Promise<void> {
        try {
            const { data } = await axios.get(route('passkey.login-options'));
            const credential = await startAuthentication({ optionsJSON: data.options });
            await axios.post(route('passkey.login'), {
                credential,
                remember: true,
            });
            window.location.href = route('dashboard');
        } catch (error: any) {
            if (error?.name === 'SecurityError' || error?.message?.includes('not supported')) {
                toast.error('Passkeys are not supported by your browser or device.');
            } else if (error?.name === 'AbortError' || error?.message?.includes('cancelled')) {
                // User cancelled the operation — no error toast needed
            } else {
                toast.error(error?.response?.data?.message ?? 'Passkey authentication failed.');
            }
            throw error;
        }
    }

    return { registerPasskey, deletePasskey, authenticate };
}
