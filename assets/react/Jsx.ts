import React from 'react';

export const Jsx = Object.freeze({
    radio: (name: string, value: string, state: string, onChange: (state: string) => void) => ({ // input radio
        type: 'radio', name, id: name + '_' + value, value, checked: state === value,
        onChange: (e: React.ChangeEvent<HTMLInputElement>) => onChange(e.currentTarget.value),
    }),

    radioLbl: (name: string, value: string) => ({htmlFor: name + '_' + value}), // label for input radio
})
