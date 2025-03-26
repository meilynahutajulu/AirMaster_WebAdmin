import Heading from '@/components/heading';
import { Separator } from '@/components/ui/separator';
import { type PropsWithChildren } from 'react';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { usePage } from '@inertiajs/react';


export default function UsersLayout({ children }: PropsWithChildren) {
    const { users, flash } = usePage().props;

    return (
        <div className="px-4 py-6">
            <Heading title="Data" description="Manage and view user information" />
            <Separator className="my-6 md:hidden" />
            <Table>
                <TableCaption>PT. Indonesia Air Asia User Account</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead className='w-[150px]'>PHOTO</TableHead>
                        <TableHead className='w-[150px]'>EMAIL</TableHead>
                        <TableHead className='w-[150px]'>NAME</TableHead>
                        <TableHead className='w-[150px]'>RANK</TableHead>
                        <TableHead className='w-[100px]'>LOA NO</TableHead>
                        <TableHead className='w-[100px]'>LICENSE NO</TableHead>
                        <TableHead className='w-[100px]'>LICENSE EXPIRY</TableHead>
                        <TableHead className='w-[80px]'>INSTRUCTOR</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                        <TableCell>Credit Card</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

        </div>
    );
}
