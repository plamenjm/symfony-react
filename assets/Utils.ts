import React, {SetStateAction} from 'react';


//--- Types

export type TSStateSetCB<S> = React.Dispatch<React.SetStateAction<S>> //(state: SetStateAction<S>) => void


//---

export const Utils = Object.freeze({
    caller: function(idx = 1) {
        try {
            //return funcName.caller.name // error: 'caller' access on strict mode...
            //noinspection ExceptionCaughtLocallyJS
            throw new Error()
        } catch (ex) {
            return ex instanceof Error
                ? ex.stack?.split('\n')[idx].split('<')[0].split('@')[0].split('/').reverse()[0]
                : '?'
        }
    },

    log: function(...args: unknown[]): boolean { // console.log in expressions for TS
        console.log(this.caller(2), ...args)
        return false
    },

    dateTimeUTC: function(date: undefined | string | number | Date = undefined) {
        if (!(date instanceof Date)) date = date ? new Date(date) : new Date()
        return date.toISOString()
            .split('T').join(' ')
            .split('.').slice(0, -1).join('')
    }
})
