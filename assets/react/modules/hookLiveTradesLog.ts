import React from 'react';
import useWebSocket, {ReadyState} from 'react-use-websocket';
import {Config} from '/assets/Config';
import {LV, TSChartTicks, TSLogMessage, TSRawMessages} from '/assets/react/modules/utilsLiveTrades';
import {TSStateSetCB} from '/assets/Utils';
import {useLiveTradesSocketIO} from '/assets/react/modules/hookLiveTradesSocketIO';

export function useLiveTradesLog(stateDate: Date, stateView: string, stateSymbol: string, ticks: TSChartTicks,
                                 setMessages: TSStateSetCB<TSLogMessage[]>,
                                 onMessage: (msg: string | TSLogMessage, uniqId?: boolean) => void, onClear: (all: boolean) => void,
                                 stateUrlLive: null | string[], onServerMsg: (eventData: string, uniqId: boolean, live: boolean) => void) {
    const uniqId = false // false: onClear(false); true: collect and filter by id
    const urlLog = Config.LiveTradesSocketIOServer ? Config.LiveTradesSocketIOLog : Config.LiveTradesUrlLog

    const [stateUrlLog, setUrlLog] = React.useState<null | string[]>(null)

    const options = React.useMemo(() => (
        {onMessage: (event: MessageEvent) => onServerMsg(event.data, uniqId, false)}
    ), [])
    const {readyState, sendMessage, lastMessage} = Config.LiveTradesSocketIOServer
        ? useLiveTradesSocketIO(stateUrlLog, uniqId, false, onServerMsg)
        : useWebSocket<TSRawMessages>(stateUrlLog?.join('/') ?? null, options)

    React.useLayoutEffect(() => { // fetch response
        if (!lastMessage) return
        setTimeout(() => setUrlLog(null), 0) // overlap stateFetch and stateCalc (delay stateFetch)
        if (Config.DevLogEnable) console.log('Fetch Response', JSON.parse(lastMessage.data))
    }, [lastMessage])

    React.useLayoutEffect(() => { // fetch request
        if (readyState !== ReadyState.OPEN) return
        const from = Math.trunc(ticks[0].value / 1000)
        const to = Math.trunc(ticks.slice(-1)[0].value / 1000) + 1
        const pair = stateSymbol === LV.EnumSymbol.USD ? LV.EnumEvent.USD
            : stateSymbol === LV.EnumSymbol.EUR ? LV.EnumEvent.EUR
                : ''
        if (uniqId || Config.DevLogEnable) setMessages(state => {
            const fr0 = from * 1000, to0 = to * 1000, newState = (!uniqId) ? state
                : state.filter(msg => !('type' in msg) || msg.type !== pair || fr0 > msg.date || msg.date >= to0)
            if (Config.DevLogEnable) console.log('Request Fetch', //(new Date(from * 1000)).toISOString(), (new Date(to * 1000)).toISOString(),
                state.length, newState.length, pair.slice(1), from, to)
            return newState
        })
        const apiLog = `{"event": "log", "channel": "trades", "pair": "${pair.slice(1)}", "from": ${from}, "to": ${to}}`
        if (urlLog) onMessage('[' + urlLog.join('/') + ' <] ' + apiLog)
        sendMessage(apiLog)
    }, [readyState])

    React.useLayoutEffect(() => { // fetch connect
        if (!stateUrlLive) return setUrlLog(null)
        if (!uniqId) onClear(false)
        setUrlLog(urlLog)
    }, [stateDate, stateView, stateSymbol, ticks, stateUrlLive])

    return {stateFetch: !!stateUrlLog}
}
