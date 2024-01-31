import React from 'react';

export const Jsx = Object.freeze({
    radio: (name: string, value: string, current: string, onChange: (value: string) => void) => ({ // input radio
        type: 'radio', name, id: name + '_' + value, value, checked: current === value,
        onChange: (el: React.ChangeEvent<HTMLInputElement>) => onChange(el.currentTarget.value),
    }),

    radioLbl: (name: string, value: string) => ({htmlFor: name + '_' + value}), // input radio label

    check: (id: string, checked: boolean, onChange: () => void) => ({type: 'checkbox', id, checked, onChange}),
})
