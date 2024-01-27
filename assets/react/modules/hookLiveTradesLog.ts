import React from 'react';
import useWebSocket from 'react-use-websocket';
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

export function onWSMessageCB(refCB: React.MutableRefObject<{
    pair: string, ticks: TSChartTicks,
    setMessages: TSStateSetCB<TSLogMessage[]>,
    onMessage: (msg: (string | TSLogMessage), uniqId?: boolean) => void,
    onEvent: (events?: TSTradeMessage[]) => void, onEvents: () => void,
}>) {
    return (event: MessageEvent, uniqId = false, live = false) => {
        // messages:
        //{ event: 'info', version: '1.1', serverId: '7b5fa247-51ef-4ac9-bd13-3630160aaab7', platform: {status: 1} }
        //[ 12430, [ [ '1494734165-tBTCUSD', 1705081544, 43532, 0.001 ] ] ]
        //[ 12430, 'te', '1494734166-tBTCUSD',             1705081553, 43535, 0.0156206 ]
        //[ 12430, 'tu', '1494734166-tBTCUSD', 1494734166, 1705081553, 43535, 0.0156206 ]

        //if (live) return // test
        const jsonMsg = JSON.parse(event.data)
        const isData = jsonMsg && (typeof jsonMsg[1] === 'object' || jsonMsg[1] === 'te')
        if (!isData) {
            refCB.current.onMessage('[' + Config.LiveTradesUrl + ' >] ' + event.data)
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

function refOnWSMessage(pair: string, ticks: TSChartTicks,
                        setMessages: TSStateSetCB<TSLogMessage[]>,
                        onMessage: (msg: (string | TSLogMessage), uniqId?: boolean) => void,
                        onEvent: (events?: TSTradeMessage[]) => void, onEvents: () => void) {
    const refCBCurrent = {pair, ticks, setMessages, onMessage, onEvent, onEvents}
    const refCB = React.useRef(refCBCurrent)
    refCB.current = refCBCurrent
    return onWSMessageCB(refCB)
}

export function useLiveTradesLog(stateDate: Date, stateView: string, stateSymbol: string, ticks: TSChartTicks,
                                 setMessages: TSStateSetCB<TSLogMessage[]>,
                                 onMessage: (msg: string | TSLogMessage, uniqId?: boolean) => void, onClear: () => void,
                                 onEvent: (events?: TSTradeMessage[]) => void, onEvents: () => void) {
    const uniqId = false // false: onClear(); true: collect and filter by id

    const [stateUrl, setUrl] = React.useState<null | string>(Config.LiveTradesAutoConnect ? Config.LiveTradesUrl : null)
    const [stateLogUrl, setLogUrl] = React.useState<null | string>(null)

    const pair = stateSymbol === LV.EnumSymbol.USD ? LV.EnumEvent.USD
        : stateSymbol === LV.EnumSymbol.EUR ? LV.EnumEvent.EUR
            : ''
    const cbOnWSMessage = React.useCallback(refOnWSMessage(pair, ticks, setMessages, onMessage, onEvent, onEvents),
        [])

    const options = React.useMemo(() => (
        {onMessage: (event: MessageEvent) => cbOnWSMessage(event, uniqId)}
    ), [])
    const {lastMessage, sendMessage} = useWebSocket<TSRawMessages>(stateLogUrl, options)

    React.useLayoutEffect(() => { // fetch response
        if (!lastMessage) return
        setTimeout(() => setLogUrl(null), 0) // overlap stateFetch and stateCalc (delay stateFetch)
        if (Config.DevLogEnable) console.log('Fetch Response', JSON.parse(lastMessage.data))
    }, [lastMessage])

    React.useLayoutEffect(() => { // request fetch
        if (!stateUrl) return
        if (!uniqId) onClear()
        setLogUrl(Config.LiveTradesLogUrl)
        const from = Math.trunc(ticks[0].value / 1000)
        const to = Math.trunc(ticks.slice(-1)[0].value / 1000) + 1

        if (uniqId || Config.DevLogEnable) setMessages(state => {
            const fr0 = from * 1000, to0 = to * 1000, newState = (!uniqId) ? state
                : state.filter(msg => !('type' in msg) || msg.type !== pair || fr0 > msg.date || msg.date >= to0)
            if (Config.DevLogEnable) console.log('Request Fetch', //(new Date(from * 1000)).toISOString(), (new Date(to * 1000)).toISOString(),
                state.length, newState.length, pair.slice(1), from, to)
            return newState
        })
        const apiLog = `{"event": "log", "channel": "trades", "pair": "${pair.slice(1)}", "from": ${from}, "to": ${to}}`
        sendMessage(apiLog)
    }, [stateDate, stateView, stateSymbol, ticks, stateUrl])

    return {stateUrl, setUrl, onWSMessage: cbOnWSMessage, stateFetch: !!stateLogUrl}
}
