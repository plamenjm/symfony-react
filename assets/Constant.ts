import {ReadyState} from 'react-use-websocket';

export const Constant = Object.freeze({
    ErrorUnexpected: 'Unexpected error.',

    WebSocketState: {
        [ReadyState.CONNECTING      ]: 'connecting',
        [ReadyState.OPEN            ]: 'open',
        [ReadyState.CLOSING         ]: 'closing',
        [ReadyState.CLOSED          ]: 'closed',
        [ReadyState.UNINSTANTIATED  ]: 'uninstantiated',
    },
    WebSocketCloseCode: 1000 as number,
})
