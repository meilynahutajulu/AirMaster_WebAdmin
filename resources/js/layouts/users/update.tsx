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
  _id: z.string(),
  ATTRIBUTE: z.string().min(2, { message: "Attribute is required." }),
  HUB: z.string().min(2, { message: "HUB is required." }),
  STATUS: z.string().min(2, { message: "Status is required" }),
  "ID NO": z.string().min(2, { message: "ID NO is required." }),
  "LOA NO": z.string().min(2, { message: "LOA NO is required." }),
  "LICENSE NO": z.string().min(2, { message: "LICENSE NO is required." }),
  TYPE: z.string().min(2, { message: "Type is required." }),
  RANK: z.string().min(2, { message: "Rank is required." }),
  "LICENSE EXPIRY": z.string().min(2, { message: "LICENSE EXPIRY is required." }),
  NAME: z.string().min(1, { message: "Name is required." }),
  EMAIL: z.string()
    .min(1, { message: "Email is required." })
    .regex(/^[\w.+-]+@airasia\.com$/, { message: "Email must be a valid airasia.com address." })
})

export function EditForm() {
  const { user } = usePage<{ user: User }>().props;

  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
    defaultValues: {
      _id: user._id || "",
      ATTRIBUTE: user.attribute || "",
      HUB: user.hub || "",
      STATUS: user.status || "",
      "ID NO": user['id_number']?.toString() || "",
      "LOA NO": user["loa_number"] || "",
      "LICENSE NO": typeof user["license_no"] === "string" ? user["license_no"] : "",
      TYPE: user.type || "",
      RANK: user.rank || "",
      "LICENSE EXPIRY": user.license_expiry ? new Date(user.license_expiry).toISOString().split('T')[0] : "",
      NAME: user.name || "",
      EMAIL: user.email || "",
    },
  })

  function onSubmit(data: z.infer<typeof FormSchema>) {
    router.post('/users', data, {
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
          {inputField("ATTRIBUTE", "Attribute", "ex: Trainee")}
          {inputField("HUB", "HUB", "ex: CGK")}
          {inputField("STATUS", "Status", "ex: NOT VALID")}
        </div>

        <div className="grid grid-cols-3 gap-4">
          {inputField("ID NO", "ID NO", "ex: 11321012")}
          {inputField("LOA NO", "LOA NO", "ex: 21012")}
          {inputField("LICENSE NO", "LICENSE NO", "ex: DEL 11321012")}
        </div>

        <div className="grid grid-cols-3 gap-4">
          {inputField("TYPE", "TYPE", "ex: Instrucor")}
          {inputField("RANK", "RANK", "ex: CAPT")}
          {inputField("LICENSE EXPIRY", "LICENSE EXPIRY", "ex: 2025-12-31")}
        </div>

        {inputField("NAME", "Name", "ex: John Doe")}
        <FormField
          control={form.control}
          name="EMAIL"
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
