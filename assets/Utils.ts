import {SetStateAction} from 'react';


//--- Types

export type TSStateSetCB<S> = (state: SetStateAction<S>) => void


//---

export const Utils = Object.freeze({
    caller: function(idx = 1) {
        try {
            //return funcName.caller.name // error: 'caller' access on strict mode...
            //noinspection ExceptionCaughtLocallyJS
            throw new Error()
        } catch (ex) {
            return (ex as Error).stack?.split('\n')[idx]?.split('@')[0] ?? '?'
        }
    },

    log: function(...args: unknown[]): boolean { // console.log in expressions for TS
        console.log(this.caller(2), ...args)
        return false
    },
})
