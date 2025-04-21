import { User } from '@/types';
import { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown } from "lucide-react"
import { Button } from './ui/button';
import DataTableRowActions from './row-actions';

interface UserColumnsProps {
  onEdit: (user: User) => void;
  onDelete: (user: User) => Promise<void>;
}

export const userColumns = ({ onEdit, onDelete }: UserColumnsProps): ColumnDef<User>[] => [
  {
    accessorKey: "photo_url",
    header: "Picture",
  },
  {
    accessorKey: "email",
    header: ({ column }) => {
      return (
        <Button
          variant="ghost"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Email
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
  },
  {
    accessorKey: "name",
    header: ({ column }) => {
      return (
        <Button
          variant="ghost"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Name
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
  },
  {
    accessorKey: "rank",
    header: ({ column }) => {
      return (
        <Button
          variant="ghost"
          onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}
        >
          Rank
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
  },
  {
    accessorKey: "instructor",
    header: "Instructor",
  },
  {
    header: "Actions",
    id: "actions",
    cell: ({ row }) => <DataTableRowActions row={row} onEdit={onEdit} onDelete={async (user) => await onDelete(user)} />,
  }
]