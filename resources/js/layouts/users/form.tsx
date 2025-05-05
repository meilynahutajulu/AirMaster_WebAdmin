"use client"
import { useEffect } from "react"
import { v4 as uuidv4 } from "uuid"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"
import { router } from '@inertiajs/react'

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
import{
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { FormDatePicker } from "@/layouts/users/date"


// Skema validasi
const FormSchema = z.object({
  _id: z.string(),
  photo_url: z.string().optional(),
  attribute: z.string().min(2, { message: "Attribute is required." }),
  hub: z.string().min(2, { message: "HUB is required." }),
  status: z.string().min(2, { message: "Status is required" }),
  id_number: z.string().min(2, { message: "ID NO is required." }),
  loa_number: z.string().min(2, { message: "LOA NO is required." }),
  license_number: z.string().min(2, { message: "LICENSE NO is required." }),
  type: z.string().min(2, { message: "Type is required." }),
  rank: z.string().min(2, { message: "Rank is required." }),
  license_expiry: z.date({ message: "LICENSE EXPIRY is required." }),
  name: z.string().min(1, { message: "Name is required." }),
  email: z.string()
    .min(1, { message: "Email is required." })
    .regex(/^[\w.+-]+@airasia\.com$/, { message: "Email must be a valid airasia.com address." }), 
})

export function InputForm() {
  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
    defaultValues: {
      _id: "",
      photo_url: "",
      attribute: "",
      hub: "",
      status: "",
      id_number: "",
      loa_number: "",
      license_number: "",
      type: "",
      rank: "",
      license_expiry: new Date(),
      name: "",
      email: "",
    },
  })

  useEffect(() => {
    const generatedId = uuidv4()
    form.setValue("_id", generatedId)
  }, [form])

  function onSubmit(data: z.infer<typeof FormSchema>) {
    router.post('store', data, {
      onSuccess: () => {
        toast.success("User berhasil disimpan!");
        form.reset(); // reset form setelah berhasil
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
            <Input placeholder={placeholder} {...field} value={field.value instanceof Date ? field.value.toISOString().split('T')[0] : field.value} />
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
              <FormDatePicker field={field} label="License Expiry" />
            )}
          />
          
        </div>

        <div className="grid grid-cols-3 gap-4">
          {inputField("id_number", "ID NO", "ID Number")}
          {inputField("loa_number", "LOA NO", "LOA Number")}
          {inputField("license_number", "LICENSE NO", "License Number")}
        </div>

        <div className="grid grid-cols-3 gap-4">
          {selectField("type", "TYPE", "Type",[
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
          {selectField("status", "Status", "Status", [
            { label: "Invalid", value: "INVALID" },
            { label: "Valid", value: "VALID" },
            ])}
        </div>


        <Button type="submit">Submit</Button>
      </form>
    </Form>
  )
}
