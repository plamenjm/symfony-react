import React from 'react';
import {io, Socket} from 'socket.io-client';
import {ReadyState} from 'react-use-websocket';
import {Config} from '/assets/Config';
import DisconnectReason = Socket.DisconnectReason;
import {DisconnectDescription} from 'socket.io-client/build/esm-debug/socket';
import {TSStateSetCB} from '/assets/Utils';
import {TSLogMessage} from '/assets/react/modules/utilsLiveTrades';

export function useLiveTradesSocketIO(stateUrl: null | string[], uniqId: boolean, live: boolean,
                                      onServerMsg: (eventData: string, uniqId: boolean, live: boolean) => void,
                                      setCloseCode?: TSStateSetCB<number>) {
    const refSocket = React.useRef<undefined | Socket>(undefined)
    const [readyState, setReady] = React.useState(ReadyState.UNINSTANTIATED)
    const [lastMessage, setLastMessage] = React.useState<undefined | {data: string}>(undefined)

    function sendMessage(message: string) {
        if (!refSocket.current || !stateUrl) return
        refSocket.current.emit(stateUrl[1], message)
    }

    React.useLayoutEffect(() => {
        function onConnect() {
            setReady(ReadyState.OPEN)
        }

        function onDisconnect(reason: DisconnectReason) {
            if (setCloseCode) setCloseCode(reason === 'io client disconnect' ? 1000 : 1)
            setReady(ReadyState.CLOSED)
        }

        function onMessage(data: string) {
            onServerMsg(data, uniqId, live)
            setLastMessage({data})
        }

        if (stateUrl && !refSocket.current) {
            refSocket.current = io(stateUrl[0]) //{autoConnect: false}
            refSocket.current.on('connect', onConnect)
            refSocket.current.on('disconnect', onDisconnect)
            refSocket.current.on(stateUrl[1], onMessage)
            setReady(ReadyState.CONNECTING)
        } else if (!stateUrl && refSocket.current) {
            setReady(ReadyState.CLOSING)
            const url = live ? Config.LiveTradesSocketIOLive : Config.LiveTradesSocketIOLog
            refSocket.current.off('disconnect', onDisconnect)
            refSocket.current.off('connect', onConnect)
            refSocket.current.off(url[1], onMessage)
            refSocket.current.disconnect()
            refSocket.current = undefined
        }
    }, [stateUrl])

    return {readyState, sendMessage, lastMessage}
}
