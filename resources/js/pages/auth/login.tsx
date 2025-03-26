import { LoginForm } from '@/layouts/auth/layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { toast } from 'sonner'
import { Toaster } from "@/components/ui/sonner"
import { useEffect } from 'react';

export default function Login() {
	const { flash } = usePage<SharedData>().props;

	useEffect(() => {
		if (flash?.error) {
			toast.error(flash.error);
		}
		if (flash?.success) {
			toast.success(flash.success);
		}
	}, [flash])

	return (
		<>
			<Head title="Welcome">
				<link rel="preconnect" href="https://fonts.bunny.net" />
				<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
			</Head>
			<div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
				<div className="flex w-full max-w-sm flex-col gap-6">
					<Toaster position="bottom-right" richColors />
					<LoginForm />
				</div>
			</div>
		</>
	);
}
