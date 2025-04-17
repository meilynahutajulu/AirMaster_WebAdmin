import { User } from '@/types';
import { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown } from "lucide-react"
import { Button } from './ui/button';
import DataTableRowActions from './row-actions';

interface UserColumnsProps {
  onEdit: (user: User) => void;
  onDelete: (user: User) => void;
}

export const userColumns = ({ onEdit, onDelete }: UserColumnsProps): ColumnDef<User>[] => [
  {
    accessorKey: "PHOTOURL",
    header: "Picture",
  },
  {
    accessorKey: "EMAIL",
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
    accessorKey: "NAME",
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
    accessorKey: "RANK",
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
    accessorKey: "INSTRUCTOR",
    header: "Instructor",
  },
  {
    header: "Actions",
    id: "actions",
    cell: ({ row }) => <DataTableRowActions row={row} onEdit={onEdit} onDelete={onDelete} />,
  }
]