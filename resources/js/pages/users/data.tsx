import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/react'; // Import Inertia's router
import AppLayout from '@/layouts/app-layout';
import UsersLayout from '@/layouts/users/layout';
import { userColumns } from '@/components/user-columns';
import { DataTable } from '@/layouts/users/user-table';
import { buttonVariants } from '@/components/ui/button';
import { User2Icon } from 'lucide-react';
import { useCallback, useMemo } from 'react';
import CryptoJS from 'crypto-js';
import { toast } from 'sonner'
import { Toaster } from "@/components/ui/sonner"
import { useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Users Data',
		href: '/users/index',
	},
];

export default function UserData() {
	const { flash } = usePage<SharedData>().props;
	const { users } = usePage<{ users: User[] }>().props;

	useEffect(() => {
		if (flash?.error) {
			toast.error(flash.error);
		}
		if (flash?.success) {
			toast.success(flash.success);
		}
	}, [flash])


	const key = CryptoJS.enc.Utf8.parse('1234567890123456');
	const iv = CryptoJS.enc.Utf8.parse('abcdef9876543210');

	const onDelete = useCallback(
		async (user: User) => {
			console.log(user.id_number);
			await router.post(`/users/delete/${user.id_number}`);
		},
		[],
	);

	const base64UrlEncode = (base64: string) =>
		base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

	const onEdit = useCallback(
		(user: User) => {
			const message = user['id_number'].toString();
			const encrypted = CryptoJS.AES.encrypt(message, key, {
				iv: iv,
				mode: CryptoJS.mode.CBC,
				padding: CryptoJS.pad.Pkcs7
			}).toString();

			const urlSafeEncrypted = base64UrlEncode(encrypted);

			console.log(`Edit ${urlSafeEncrypted}`);
			router.visit(`/users/edit/${urlSafeEncrypted}`);
		},
		[],
	);


	const columns = useMemo(() => userColumns({ onEdit, onDelete }), []);

	return (
		< AppLayout breadcrumbs={breadcrumbs} >
			<Head title="Users Data" />
			<UsersLayout>
				<Toaster position="bottom-right" richColors />
				<Link className={buttonVariants({ variant: "default", className: "mb-2" })} href={'users/add'}><User2Icon />Add User</Link>
				<DataTable columns={columns} data={users} />
			</UsersLayout>
		</AppLayout >
	);
}