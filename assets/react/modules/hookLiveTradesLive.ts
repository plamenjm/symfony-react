import React from 'react';
import useWebSocket, {ReadyState} from 'react-use-websocket';
import {Constant} from '/assets/Constant';
import {Config, DevFaker} from '/assets/Config';
import {TSStateSetCB} from '/assets/Utils';
import {LV, TSLogMessage, TSRawMessages, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';


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

export function useLiveTradesLive(stateUrl: null | string, setUrl: TSStateSetCB<null | string>,
                                  onWSMessage: (event: MessageEvent, uniqId: boolean, live: boolean) => void,
                                  setMessages: TSStateSetCB<TSLogMessage[]>,
                                  onMessage: (msg: string | TSLogMessage) => void, onClear: (all: boolean) => void,
                                  isClear: boolean, onEvents: () => void) {
    const [stateCloseCode, setCloseCode] = React.useState(0)
    //const [stateStatus, setStatus] = React.useState(ReadyState.UNINSTANTIATED)

    const cbOnClose = React.useCallback((event: CloseEvent) => setCloseCode(event.code),
        [])

    const options = React.useMemo(() => (
        {onMessage: (event: MessageEvent) => onWSMessage(event, false, true), onClose: cbOnClose}
    ), [])
    const {readyState, sendMessage} = useWebSocket<TSRawMessages>(stateUrl,options)

    React.useLayoutEffect(() => {
        if (!stateCloseCode || stateCloseCode === 1000) return
        onMessage('... reconnect (close code: ' + stateCloseCode + ')')
        const timeout = setTimeout(() => {
            setUrl(Config.LiveTradesUrl)
            setCloseCode(0)
        }, 3000);
        return () => clearTimeout(timeout);
    }, [stateCloseCode])

    React.useLayoutEffect(() => {
        if (readyState == ReadyState.UNINSTANTIATED) return

        //if (readyState !== stateStatus) {
        //    setStatus(readyState)
            onMessage('[' +Config.LiveTradesUrl + ' ' + Constant.WebSocketState[readyState] + ']')
        //}

        if (readyState === ReadyState.CLOSED)
            setUrl(null)
        else if (readyState === ReadyState.OPEN)
            Config.LiveTradesSubscribe.forEach(subscribe => {
                if (Config.LiveTradesShowRequests) onMessage('[' +Config.LiveTradesUrl + ' <] ' + subscribe)
                sendMessage(subscribe)
            })
    }, [readyState])

    React.useLayoutEffect(() => {
        if (!DevFaker || stateUrl || !isClear) return
        setMessages(fakerGetEvents())
        onEvents()
    }, [stateUrl, isClear])

    const onConnect = React.useCallback(() => {
        onClear(true)
        setUrl(Config.LiveTradesUrl)
    }, [])

    const onDisconnect = React.useCallback(() => {
        setUrl(null)
        setCloseCode(0)
    }, [])

    const isReconnect = ![0, 1000].includes(stateCloseCode)

    return {stateUrl, onClear, onConnect, onDisconnect, isReconnect}
}
