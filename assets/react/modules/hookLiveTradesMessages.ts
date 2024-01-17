import React from 'react';
import {Config} from '/assets/Config';
import {TSStateSetCB} from '/assets/Utils';
import {TSLogMessage, TSRawMessages, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';

export function rawMessagesToEvents(jsonArr: TSRawMessages[], onEvent: (event: TSTradeMessage) => void) {
    jsonArr.forEach(data => {
        const idx = 0, idxD = 1, idxP = 2, idxA = 3, id = data[idx].split('-')
        onEvent({id: id[0], type: id[1], date: +data[idxD] * 1000, price: +data[idxP] / 1000, amount: +data[idxA]})
    })
}

export function useLiveTradesMessages(lastMessage: null | MessageEvent, lastJsonMessage: TSRawMessages,
                                      setMessages: TSStateSetCB<TSLogMessage[]>, onMessage: (msg: TSLogMessage) => void,
                                      onEvents: (events?: TSTradeMessage[]) => void) {
    React.useLayoutEffect(() => {
        // messages:
        //{ event: 'info', version: '1.1', serverId: '7b5fa247-51ef-4ac9-bd13-3630160aaab7', platform: {status: 1} }
        //[ 12430, [ [ '1494734165-tBTCUSD', 1705081544, 43532, 0.001 ] ] ]
        //[ 12430, 'te', '1494734166-tBTCUSD',             1705081553, 43535, 0.0156206 ]
        //[ 12430, 'tu', '1494734166-tBTCUSD', 1494734166, 1705081553, 43535, 0.0156206 ]

        const jsonMsg = lastJsonMessage
        const isData = jsonMsg && (Array.isArray(jsonMsg[1]) || jsonMsg[1] === 'te')
        if (!isData && lastMessage)
            onMessage({data: (new Date()).toISOString() + ' [' +Config.LiveTradesUrl + ' >] ' + lastMessage.data})

        if (!isData) return
        const jsonArr = (Array.isArray(jsonMsg[1]) ? (jsonMsg[1] as TSRawMessages[]) : [jsonMsg.slice(2)])
        rawMessagesToEvents(jsonArr, (event) => {
            onMessage(event)
            onEvents([event])
        })
    }, [lastMessage, lastJsonMessage])
}
