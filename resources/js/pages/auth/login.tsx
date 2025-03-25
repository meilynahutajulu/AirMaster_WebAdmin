import { LoginForm } from '@/layouts/auth/layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
	const { auth } = usePage<SharedData>().props;

	return (
		<>
			<Head title="Welcome">
				<link rel="preconnect" href="https://fonts.bunny.net" />
				<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
			</Head>
			<div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
				<div className="flex w-full max-w-sm flex-col gap-6">
					<LoginForm />
				</div>
			</div>
		</>
	);
}
