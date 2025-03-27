import { User } from '@/types';
import { ColumnDef } from '@tanstack/react-table';
import { ArrowUpDown } from "lucide-react"
import { Button } from './ui/button';

export const userColumns: ColumnDef<User>[] = [
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
    accessorKey: "LOA NO",
    header: "LOA No",
  },
  {
    accessorKey: "LICENSE NO.",
    header: "License No",
  },
  {
    accessorKey: "INSTRUCTOR",
    header: "Instructor",
  }
]