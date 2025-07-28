import { useState, useTransition } from "react"
import { Row } from "@tanstack/react-table"
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator
} from "@/components/ui/dropdown-menu"
import { Button } from "@/components/ui/button"
import { MoreHorizontal } from "lucide-react"
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface DataTableRowActionsProps<User> {
  row: Row<User>;
  onEdit: (value: User) => void;
  onDelete: (value: User) => Promise<void>; // ubah menjadi async function
}

const DataTableRowActions = <User,>({ row, onEdit, onDelete }: DataTableRowActionsProps<User>) => {
  const [open, setOpen] = useState(false)
  const [isPending, startTransition] = useTransition()

  const handleDelete = () => {
    startTransition(() => {
      onDelete(row.original).then(() => {
        setOpen(false)
      })
    })
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogContent>
        <DialogTitle>Are you sure you want to delete this account?</DialogTitle>
        <DialogDescription>
          Deleting this account will permanently remove all associated data and resources. Are you sure you want to proceed?

        </DialogDescription>
        <DialogFooter className="gap-2">
          <DialogClose asChild>
            <Button variant="outline" disabled={isPending}>
              Cancel
            </Button>
          </DialogClose>
          <Button
            variant="destructive"
            onClick={handleDelete}
            disabled={isPending}
          >
            {isPending ? 'Deleting...' : 'Confirm'}
          </Button>
        </DialogFooter>
      </DialogContent>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button variant={"ghost"} className="h-8 w-8 p-0 data-[state=open]:bg-muted">
            <MoreHorizontal className="h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem onClick={() => onEdit(row.original)}>
            Edit
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          <DialogTrigger className="w-full">
            <DropdownMenuItem variant="destructive">
              Delete
            </DropdownMenuItem>
          </DialogTrigger>
        </DropdownMenuContent>
      </DropdownMenu>
    </Dialog>
  )
}

export default DataTableRowActions;
