import { useEffect } from "react"
import { v4 as uuidv4 } from "uuid"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { router, usePage } from '@inertiajs/react'

import { Button } from "@/components/ui/button"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"
import { Input } from "@/components/ui/input"
import { User } from "@/types"

// Skema validasi
const FormSchema = z.object({
  attribute: z.string().min(2, { message: "Attribute is required." }),
  hub: z.string().min(2, { message: "HUB is required." }),
  status: z.string().min(2, { message: "Status is required" }),
  id_number: z.string().min(2, { message: "ID NO is required." }),
  loa_number: z.string().min(2, { message: "LOA NO is required." }),
  license_number: z.string().min(2, { message: "LICENSE NO is required." }),
  type: z.string().min(2, { message: "Type is required." }),
  rank: z.string().min(2, { message: "Rank is required." }),
  license_expiry: z.string().min(2, { message: "LICENSE EXPIRY is required." }),
  name: z.string().min(1, { message: "Name is required." }),
  email: z.string()
    .min(1, { message: "Email is required." })
    .regex(/^[\w.+-]+@airasia\.com$/, { message: "Email must be a valid airasia.com address." })
})  

export function EditForm() {
  const { user } = usePage<{ user: User }>().props;

  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
    defaultValues: {
      attribute: user.attribute || "",
      hub: user.hub || "",
      status: user.status || "",
      id_number: user['id_number']?.toString() || "",
      loa_number: user["loa_number"] || "",
      license_number: user["license_number"] || "",
      type: user.type || "",
      rank: user.rank || "",
      license_expiry: user.license_expiry || "",
      name: user.name || "",
      email: user.email || "",
    },
  })

  function onSubmit(data: z.infer<typeof FormSchema>) {
    router.post(route('update', data.id_number), data, {
      onSuccess: () => {
        toast.success("User berhasil disimpan!");
        form.reset();
      },
      onError: (errors) => {
        toast.error("Gagal menyimpan user!");
        console.error(errors)
      },
    });
  }


  const inputField = (name: keyof z.infer<typeof FormSchema>, label: string, placeholder: string) => (
    <FormField
      control={form.control}
      name={name}
      render={({ field }) => (
        <FormItem>
          <FormLabel>{label}</FormLabel>
          <FormControl>
            <Input placeholder={placeholder} {...field} />
          </FormControl>
          <FormMessage />
        </FormItem>
      )}
    />
  )

  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="w-2/3 space-y-6">
        <div className="grid grid-cols-3 gap-4">
          {inputField("attribute", "Attribute", "ex: Trainee")}
          {inputField("hub", "HUB", "ex: CGK")}
          {inputField("status", "Status", "ex: NOT VALID")}
        </div>

        <div className="grid grid-cols-3 gap-4">
          {inputField("id_number", "ID NO", "ex: 11321012")}
          {inputField("loa_number", "LOA NO", "ex: 21012")}
          {inputField("license_number", "LICENSE NO", "ex: DEL 11321012")}
        </div>

        <div className="grid grid-cols-3 gap-4">
          {inputField("type", "TYPE", "ex: Instrucor")}
          {inputField("rank", "RANK", "ex: CAPT")}
          {inputField("license_expiry", "LICENSE EXPIRY", "ex: 2025-12-31")}
        </div>

        {inputField("name", "Name", "ex: John Doe")}
        <FormField
          control={form.control}
          name="email"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Email</FormLabel>
              <FormControl>
                <Input placeholder={user.email} {...field} />
              </FormControl>
              <p className="text-sm text-muted-foreground">Only AirAsia email addresses are allowed.</p>
              <FormMessage />
            </FormItem>
          )}
        />

        <Button type="submit">Submit</Button>
      </form>
    </Form>
  )
}
