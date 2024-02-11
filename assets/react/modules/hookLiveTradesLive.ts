import React from 'react';
import useWebSocket, {ReadyState} from 'react-use-websocket';
import {Constant} from '/assets/Constant';
import {Config, DevFaker} from '/assets/Config';
import {TSStateSetCB} from '/assets/Utils';
import {LV, TSLogMessage, TSRawMessages, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';
import {useLiveTradesSocketIO} from '/assets/react/modules/hookLiveTradesSocketIO';


//---

export function fakerGetEvents(startIdx: number = 0, days = 7, perDay = 24): TSTradeMessage[] {
    // events:
    //{id: '1495595527', type: 'tBTCUSD', date: 1705250261189, price: 43, amount: 0.00034563}
    //{id: '1495595665', type: 'tBTCEUR', date: 1705250261189, price: 39.2, amount: 0.00327104}

    const from = new Date(), to = new Date()
    from.setDate(from.getDate() - days)
    return Array.from(Array(days * perDay)).map((_, idx, arr) => {
        idx = arr.length - idx + startIdx + 1 // reverse
        if (!DevFaker) return {idx, type: '', date: 0, price: 0, amount: 0} // dummy

        const type = [LV.EnumEvent.USD, LV.EnumEvent.EUR], pMin = [42900, 39100], pMax = [43100, 39300]
        const sIdx = DevFaker.number.int({min: 0, max: 1})

        const date = DevFaker.date.between({from, to}).getTime()
        const price = DevFaker.number.int({min: pMin[sIdx], max: pMax[sIdx]}) / 1000 //DevFaker.number.float({min: 42.9, max: 43.1, precision: 3})
        const amount = DevFaker.number.int({min: -800000, max: 800000}) / 100000000 //DevFaker.number.float({min: -0.08, max: 0.08, precision: 8})
        return {idx, type: type[sIdx], date, price, amount}
    })
}


//---

export function useLiveTradesLive(setMessages: TSStateSetCB<TSLogMessage[]>,
                                  onMessage: (msg: string | TSLogMessage) => void, onClear: (all: boolean) => void,
                                  isClear: boolean, onEvents: () => void,
                                  onServerMsg: (eventData: string, uniqId: boolean, live: boolean) => void) {
    const urlLive = Config.LiveTradesSocketIOServer ? Config.LiveTradesSocketIOLive : Config.LiveTradesUrlLive

    const [stateUrlLive, setUrlLive] = React.useState<null | string[]>(Config.LiveTradesAutoConnect ? urlLive : null)

    const [stateCloseCode, setCloseCode] = React.useState(Constant.WebSocketCloseCode)
    //const [stateReady, setReady] = React.useState(ReadyState.UNINSTANTIATED)

    const cbOnClose = React.useCallback((event: CloseEvent) => setCloseCode(event.code),
        [])

    const options = React.useMemo(() => ({
        onMessage: (event: MessageEvent) => onServerMsg(event.data, false, true),
        onClose: cbOnClose,
    }), [])
    const {readyState, sendMessage} = Config.LiveTradesSocketIOServer
        ? useLiveTradesSocketIO(stateUrlLive, false, true, onServerMsg, setCloseCode)
        : useWebSocket<TSRawMessages>(stateUrlLive?.join('/') ?? null, options)

    React.useLayoutEffect(() => {
        if (!stateCloseCode || stateCloseCode === Constant.WebSocketCloseCode) return
        onMessage('... reconnect (close code: ' + stateCloseCode + ')')
        const timeout = setTimeout(() => {
            setUrlLive(urlLive)
            setCloseCode(0)
        }, 3000)
        return () => clearTimeout(timeout)
    }, [stateCloseCode])

    React.useLayoutEffect(() => {
        if (readyState == ReadyState.UNINSTANTIATED) return

        //if (readyState !== stateReady) {
        //    setReady(readyState)
            onMessage('[' + urlLive.join('/') + ' ' + Constant.WebSocketState[readyState] + ']')
        //}

        if (readyState === ReadyState.CLOSED) {
            setUrlLive(null)
        } else if (readyState === ReadyState.OPEN)
            Config.LiveTradesSubscribe.forEach(subscribe => {
                if (Config.LiveTradesShowRequests) onMessage('[' + urlLive.join('/') + ' <] ' + subscribe)
                sendMessage(subscribe)
            })
    }, [readyState])

    React.useLayoutEffect(() => {
        if (!DevFaker || stateUrlLive || !isClear) return
        setMessages(fakerGetEvents())
        onEvents()
    }, [stateUrlLive, isClear])

    const onConnect = React.useCallback(() => {
        onClear(true)
        setUrlLive(urlLive)
    }, [])

    const onDisconnect = React.useCallback(() => {
        setUrlLive(null)
        setCloseCode(Constant.WebSocketCloseCode)
    }, [])

    const stateReconnect = stateCloseCode !== Constant.WebSocketCloseCode

    return {stateUrlLive, stateReconnect, onConnect, onDisconnect}
}
