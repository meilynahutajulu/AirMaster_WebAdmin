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
import{
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import ResponsiveDatePickers from "./date"
import dayjs from "dayjs"

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
  license_expiry: z.date({ message: "LICENSE EXPIRY is required." }),
  name: z.string().min(1, { message: "Name is required." })
    .regex(/^[a-zA-Z\s]+$/, { message: "Name must contain only letters and spaces." }),
  email: z.string()
    .min(1, { message: "Email is required." })
    .regex(/^[\w.+-]+@airasia\.com$/, { message: "Email must be a valid airasia.com address." })
})  

export function EditForm() {
  const { user } = usePage<{ user: User }>().props;

  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
    mode: "onChange",
    defaultValues: {
      attribute: user.attribute || "",
      hub: user.hub || "",
      status: user.status || "",
      id_number: user['id_number']?.toString() || "",
      loa_number: user["loa_number"] || "",
      license_number: user["license_number"] || "",
      type: user.type || "",
      rank: user.rank || "",
      license_expiry: user.license_expiry ? new Date(user.license_expiry) : undefined,
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
              <Input placeholder={placeholder} 
              {...field} 
              value={
                field.value instanceof Date 
                ? field.value.toISOString().split('T')[0] 
                : field.value
                }
                  readOnly={name === "id_number"}
                />

            </FormControl>
            <FormMessage />
          </FormItem>
        )}
      />
    )

    const selectField = (
      name: keyof z.infer<typeof FormSchema>,
      label: string,
      placeholder: string,
      options: { label: string; value: string }[]
    ) => (
      <FormField
        control={form.control}
        name={name}
        render={({ field }) => (
          <FormItem>
            <FormLabel>{label}</FormLabel>
            <FormControl>
              <Select onValueChange={field.onChange} defaultValue={field.value?.toString()}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                  {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </FormControl>
            <FormMessage />
          </FormItem>
        )}
        />
      )

  return (
        <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="w-2/3 space-y-6">
          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <Input placeholder="email@airasia.com" {...field} />
                </FormControl>
                {/* <p className="text-sm text-muted-foreground">Only AirAsia email addresses are allowed.</p> */}
                <FormMessage />
              </FormItem>
            )}
          />
  
  
          <div className="grid grid-cols-[3fr_1fr] gap-8">
            {inputField("name", "Name", "Name")}
            {selectField("rank", "RANK", "Rank",[
              { label: "CAPT", value: "CAPT" },
              { label: "OCC", value: "OCC" },
              { label: "FO", value: "FO" },
              { label: "SFO", value: "SFO" },
            ])}
  
          </div>
  
          <div className="grid grid-cols-[3fr_1fr] gap-4">
            {inputField("attribute", "Attribute", "Attribute")}
            <FormField
              control={form.control}
              name="license_expiry"
              render={({ field }) => (
                <FormItem>
                <FormLabel>License Expiry</FormLabel>
                <FormControl>
                  <ResponsiveDatePickers
                    value={field.value ? dayjs(field.value) : null}
                    onChange={(val) => field.onChange(val ? val.toDate() : null)}
                  />
                </FormControl>
                <FormMessage />
              </FormItem>
              )}
            />
            
          </div>
  
          <div className="grid grid-cols-3 gap-4">
            {inputField("id_number", "ID NO", "ex: 11321012")}
            {inputField("loa_number", "LOA NO", "ex: 21012")}
            {inputField("license_number", "LICENSE NO", "ex: DEL 11321012")}
          </div>
  
          <div className="grid grid-cols-3 gap-4">
            {selectField("type", "TYPE", "ex: Instrucor",[
              { label: "Pilot Administrator", value: "Administrator" },
              { label: "Pilot Instructor", value: "Instructor" },
              { label: "Pilot Examinee", value: "Examinee" },
              { label: "CPTS", value: "CPTS" },
              { label: "Super Admin", value: "SUPERADMIN" },
            ])}
            {selectField("hub", "HUB", "HUB",[
              { label: "CGK", value: "CGK" },
              { label: "KUL", value: "KUL" },
              { label: "SIN", value: "SIN" },
            ])}
            {selectField("status", "Status", "ex: NOT VALID", [
              { label: "Invalid", value: "INVALID" },
              { label: "Valid", value: "VALID" },
              ])}
          </div>
  
  
          <Button type="submit">Submit</Button>
        </form>
      </Form>
    )
}
