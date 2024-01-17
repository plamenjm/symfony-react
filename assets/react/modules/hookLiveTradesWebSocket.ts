import React from 'react';
import useWebSocket, {ReadyState} from 'react-use-websocket';
import {Constant} from '/assets/Constant';
import {Config, DevFaker} from '/assets/Config';
import {TSStateSetCB} from '/assets/Utils';
import {TSLogMessage, TSRawMessages, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';

export function fakerGetEvents(msgIdx: number, days = 28, perDay = 48): TSTradeMessage[] {
    // events:
    //{id: '1495595527', type: 'tBTCUSD', date: 1705250261189, price: 43, amount: 0.00034563}
    //{id: '1495595665', type: 'tBTCEUR', date: 1705250261189, price: 39.2, amount: 0.00327104}

    const from = new Date(), to = new Date()
    from.setDate(from.getDate() - days)
    return Array.from(Array(days * perDay)).map((_, idx) => {
        if (!DevFaker) return {idx: 1 + msgIdx + idx, type: '', date: 0, price: 0, amount: 0} // dummy

        const type = [Config.LiveTradesSymbol.USD, Config.LiveTradesSymbol.EUR], pMin = [42900, 39100], pMax = [43100, 39300]
        const sIdx = DevFaker.number.int({min: 0, max: 1})

        const date = DevFaker.date.between({from, to}).getTime()
        const price = DevFaker.number.int({min: pMin[sIdx], max: pMax[sIdx]}) / 1000 //DevFaker.number.float({min: 42.9, max: 43.1, precision: 3})
        const amount = DevFaker.number.int({min: -800000, max: 800000}) / 100000000 //DevFaker.number.float({min: -0.08, max: 0.08, precision: 8})
        return {idx: 1 + msgIdx + idx, type: type[sIdx], date, price, amount}
    })
}

export function useLiveTradesWebSocket(noMessages: boolean, setMessages: TSStateSetCB<TSLogMessage[]>, onMessage: (msg: TSLogMessage) => void,
                                       onClear: () => void, onProcess: () => void) {
    const [stateUrl, setUrl] = React.useState<null | string>(Config.LiveTradesAutoConnect ? Config.LiveTradesUrl : null)
    //const [stateStatus, setStatus] = React.useState(ReadyState.UNINSTANTIATED)
    const {lastMessage, lastJsonMessage, readyState, sendMessage} = useWebSocket<TSRawMessages>(stateUrl)

    function onConnect() {
        onClear()
        setUrl(Config.LiveTradesUrl)
    }

    const onDisconnect = () => setUrl(null)

    React.useLayoutEffect(() => {
        if (readyState == ReadyState.UNINSTANTIATED) return

        //if (readyState !== stateStatus) {
        //    setStatus(readyState)
            onMessage({data: (new Date()).toISOString() + ' [' +Config.LiveTradesUrl + ' ' + Constant.WebSocketState[readyState] + ']'})
        //}

        if (readyState === ReadyState.CLOSED)
            setUrl(null)
        else if (readyState === ReadyState.OPEN)
            Config.LiveTradesSubscribe.forEach(subscribe => {
                onMessage({data: (new Date()).toISOString() + ' [' +Config.LiveTradesUrl + ' <] ' + subscribe})
                sendMessage(subscribe)
            })
    }, [readyState])

    React.useLayoutEffect(() => {
        if (!DevFaker || stateUrl || !noMessages) return
        setMessages(fakerGetEvents(0))
        onProcess()
    }, [stateUrl, noMessages])

    return {stateUrl, onClear, onConnect, onDisconnect, lastMessage, lastJsonMessage}
}
