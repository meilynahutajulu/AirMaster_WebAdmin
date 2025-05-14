import * as React from 'react';
import dayjs from 'dayjs';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { MobileDatePicker } from '@mui/x-date-pickers/MobileDatePicker';

interface ResponsiveDatePickersProps {
  value: dayjs.Dayjs | null;
  onChange: (date: dayjs.Dayjs | null) => void;
}

export default function ResponsiveDatePickers({ value, onChange }: ResponsiveDatePickersProps) {
  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <MobileDatePicker
        value={value}
        onChange={onChange}
        slotProps={{
          textField: {
            size: 'small' // atau width: '100%', '20rem', dll
          },
        }}
      />
    </LocalizationProvider>
  );
}
