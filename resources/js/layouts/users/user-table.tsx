import {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table";

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

import { Input } from "@/components/ui/input"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useInitials } from '@/hooks/use-initials';
import { User } from "@/types";
import React from "react";
import { DataTablePagination } from "@/components/table-pagination";
import { DataTableViewOptions } from "@/components/column";

interface DataTableProps<TData, TValue> {
  columns: ColumnDef<User, any>[];
  data: User[];
}

export function DataTable<TData extends User, TValue>({
  columns,
  data,
}: DataTableProps<TData, TValue>) {
  const getInitials = useInitials();
  const [sorting, setSorting] = React.useState<SortingState>([])
  const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>(
    []
  )

  const table = useReactTable({
    data,
    columns,
    onSortingChange: setSorting,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    onColumnFiltersChange: setColumnFilters,
    getFilteredRowModel: getFilteredRowModel(),
    state: {
      sorting,
      columnFilters,
    },
  });

  return (
    <>
      <div className="rounded-md border p-3">
        <div className="flex items-center py-4">
          <Input
            placeholder="Filter emails..."
            value={(table.getColumn("EMAIL")?.getFilterValue() as string) ?? ""}
            onChange={(event) =>
              table.getColumn("EMAIL")?.setFilterValue(event.target.value)
            }
            className="max-w-sm"
          />
          <DataTableViewOptions table={table} />
        </div>

        <Table className="my-2">
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => (
                  <TableHead key={header.id}>
                    {header.isPlaceholder
                      ? null
                      : flexRender(header.column.columnDef.header, header.getContext())}
                  </TableHead>
                ))}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow key={row.id}>
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>
                      {cell.column.id === "PHOTOURL" ? (
                        <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                          <AvatarImage src={cell.getValue() as string} alt={row.original.NAME} />
                          <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                            {getInitials(row.original.NAME)}
                          </AvatarFallback>
                        </Avatar>
                      ) : cell.column.id === "LICENSE NO_" ? (
                        <>{row.original["LICENSE NO."] || "-"}</>
                      ) : (
                        cell.getValue() === null || cell.getValue() === undefined || cell.getValue() === "" ? (
                          "-"
                        ) : (
                          flexRender(cell.column.columnDef.cell, cell.getContext())
                        )
                      )}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center">
                  No results.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>

        <DataTablePagination table={table} />
      </div>
    </>
  );
}