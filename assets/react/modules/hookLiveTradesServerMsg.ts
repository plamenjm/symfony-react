import React from 'react';
import {Config} from '/assets/Config';
import {LV, TSChartTicks, TSLogMessage, TSRawMessages, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';
import {TSStateSetCB} from '/assets/Utils';


//---

export function rawMessagesToEvents(jsonArr: TSRawMessages[], onEvent: (event: TSTradeMessage) => void) {
    jsonArr.forEach(data => {
        const idx = 0, idxD = 1, idxP = 2, idxA = 3, id = data[idx].split('-')
        onEvent({id: id[0], type: id[1], date: +data[idxD] * 1000, price: +data[idxP] / 1000, amount: +data[idxA]})
    })
}

export function onServerMsgCB(refCB: React.MutableRefObject<{
    pair: string, ticks: TSChartTicks,
    setMessages: TSStateSetCB<TSLogMessage[]>,
    onMessage: (msg: (string | TSLogMessage), uniqId?: boolean) => void,
    onEvent: (events?: TSTradeMessage[]) => void, onEvents: () => void,
}>) {
    return (eventData: string, uniqId: boolean, live: boolean) => {
        // messages:
        //{ event: 'info', version: '1.1', serverId: '7b5fa247-51ef-4ac9-bd13-3630160aaab7', platform: {status: 1} }
        //[ 12430, [ [ '1494734165-tBTCUSD', 1705081544, 43532, 0.001 ] ] ]
        //[ 12430, 'te', '1494734166-tBTCUSD',             1705081553, 43535, 0.0156206 ]
        //[ 12430, 'tu', '1494734166-tBTCUSD', 1494734166, 1705081553, 43535, 0.0156206 ]

        //if (live) return // test
        const jsonMsg = JSON.parse(eventData)
        const isData = jsonMsg && (typeof jsonMsg[1] === 'object' || jsonMsg[1] === 'te')
        if (!isData) {
            refCB.current.onMessage('[' + (live ? Config.LiveTradesUrlLive : Config.LiveTradesUrlLog).join('/') + ' >] ' + eventData)
            return
        }

        let data = Array.isArray(jsonMsg[1]) ? jsonMsg[1]
            : typeof jsonMsg[1] === 'object' ? Object.values(jsonMsg[1])
                : [jsonMsg.slice(2)]
        if (live) data = data.filter(msg => !('type' in msg) || (msg.type === refCB.current.pair
            && refCB.current.ticks[0].value <= msg.date && msg.date < refCB.current.ticks.slice(-1)[0].value))
        if (!data.length) return

        if (data.length > 1) {
            rawMessagesToEvents(data, (event) => refCB.current.onMessage(event, uniqId))
            refCB.current.onEvents()
        } else
            rawMessagesToEvents(data, (event) => {
                refCB.current.onMessage(event, uniqId)
                refCB.current.onEvent([event])
            })

        if (Config.DevLogEnable) refCB.current.setMessages(state => {
            console.log('Response onMessage', live, data.length, state.length)
            return state
        })
    }
}


//---

export function useLiveTradesServerMsg(stateSymbol: string, ticks: TSChartTicks,
                                       setMessages: TSStateSetCB<TSLogMessage[]>,
                                       onMessage: (msg: string | TSLogMessage, uniqId?: boolean) => void,
                                       onEvent: (events?: TSTradeMessage[]) => void, onEvents: () => void) {
    const pair = stateSymbol === LV.EnumSymbol.USD ? LV.EnumEvent.USD
        : stateSymbol === LV.EnumSymbol.EUR ? LV.EnumEvent.EUR
            : ''

    const refCBCurrent = {pair, ticks, setMessages, onMessage, onEvent, onEvents}
    const refCB = React.useRef(refCBCurrent)
    refCB.current = refCBCurrent

    const cbOnServerMsg = React.useCallback(onServerMsgCB(refCB), [])

    return {onServerMsg: cbOnServerMsg}
}
