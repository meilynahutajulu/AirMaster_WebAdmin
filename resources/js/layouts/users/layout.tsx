import Heading from '@/components/heading';
import { Separator } from '@/components/ui/separator';
import { type PropsWithChildren } from 'react';


export default function UsersLayout({ children }: PropsWithChildren) {

    return (
        <div className="px-4 py-6">
            <Heading title="Users" description="Manage and view user information" />
            <Separator className="my-6 md:hidden" />

            {children}

        </div>
    );
}
