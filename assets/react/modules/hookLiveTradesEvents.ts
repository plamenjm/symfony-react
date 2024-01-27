import React from 'react';
import {Config} from '/assets/Config';
import {TSStateSetCB, Utils} from '/assets/Utils';
import {LV, TSChartTicks, TSEventsView, TSLogMessage, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';


//---

const keepMessages = Config.LiveTradesKeepMessages < 2 ? 1 : Config.LiveTradesKeepMessages


//---

const isSymbolType = (symbol: string, type: string) =>
    symbol === LV.EnumSymbol.USD && type === LV.EnumEvent.USD
    || symbol === LV.EnumSymbol.EUR && type === LV.EnumEvent.EUR

const eventsFilterSymbol = (events: TSTradeMessage[], symbol: string) =>
    events.filter(event => isSymbolType(symbol, event.type))

const eventsFilterDate = (events: TSTradeMessage[], from: number, to: number) =>
    events.filter(event => event.date > from && event.date <= to)

export function eventsAggregate(events: TSTradeMessage[], view: string) {
    const tickDotsHour = 10, tickDotsDay = 5, tickDotsWeek = 20
    const delta = view === LV.EnumView.Hour ? (60 / LV.ChartTicks.Hour) * 60 * 1000 / tickDotsHour
        : view === LV.EnumView.Day ? (24 / LV.ChartTicks.Day) * 60 * 60 * 1000 / tickDotsDay
            : view === LV.EnumView.Week ? (7 / LV.ChartTicks.Week) * 24 * 60 * 60 * 1000 / tickDotsWeek
                : 0
    return events.reduce((res, event) => {
        const ev = res.length && {...res.slice(-1)[0]}
        if (!ev || event.date - ev.date > delta)
            res.push(event as never)
        else {
            //ev.price = (ev.price + event.price) / 2
            //ev.amount += event.amount

            ev.price = (ev.price * Math.abs(ev.amount) + event.price * Math.abs(event.amount)) / (Math.abs(ev.amount) + Math.abs(event.amount))
            ev.amount += event.amount

            res[res.length - 1] = ev
        }
        return res
    }, [] as TSTradeMessage[])
}

export function processEvents(events: TSTradeMessage[],
                              view: string, symbol: string, aggregate: boolean,
                              from: number, to: number,
                              state?: TSEventsView): undefined | TSEventsView {
    const eventsSymbol = eventsFilterSymbol(events, symbol)
    if (!eventsSymbol.length) return

    const min = Math.min(...(!state ? [] : [state.min]), ...eventsSymbol.map(event => event.price))
    const max = Math.max(...(!state ? [] : [state.max]), ...eventsSymbol.map(event => event.price))
    const sum = eventsSymbol.map(event => event.price).reduce((res, price) => res + price, 0)
    const count = (!state ? 0 : state.count) + eventsSymbol.length
    const avg = ((!state ? 0 : state.avg * state.count) + sum) / count

    const eventsView = eventsFilterDate(eventsSymbol, from, to)
    if (!eventsView.length) return
    //console.log('eventsView', (new Date(from)).toISOString(), (new Date(to)).toISOString(), eventsView
    //    .filter((o, idx) => idx === 0 || idx === eventsView.length - 1)
    //    .map(({date}) => (new Date(date)).toISOString()))

    if (state) eventsView.push(...state.data)
    eventsView.sort((e1, e2) => e1.date - e2.date)

    //if (DevFaker) {
    //    const idStart = 1555000555
    //    eventsView.forEach((event, idx) => event.id = '' + (idStart + idx))
    //}

    return {min, max, avg, count,
        data: aggregate ? eventsAggregate(eventsView, view) : eventsView}
}


//---

export function getMessages(state: TSLogMessage[], allEvents: boolean) {
    //const keepMessages = 1000 // test
    return [
        ...state.slice(0, keepMessages),
        ...(!allEvents ? [{idx: -1, data: '...'}] : state.slice(keepMessages, -keepMessages).filter(msg => !('data' in msg))),
        ...(state.length - keepMessages <= 0 ? [] : state.slice(-Math.min(keepMessages, state.length - keepMessages))),
    ]
}

export function onMessageCB(setMessages: TSStateSetCB<TSLogMessage[]>) {
    return (message: string | TSLogMessage, uniqId = false) => {
        const msg = typeof message !== 'string' ? message : {data: Utils.dateTimeUTC() + ' ' + message}
        setMessages(state => {
            let old = getMessages(state, true)
            if (uniqId) old = old.filter(m => !('id' in msg) || !('id' in m) || m.id !== msg.id || m.type !== msg.type)
            return [{...msg, idx: 1 + (state[0]?.idx ?? 0)}, ...old]
        })
    }
}

export function onEventCB(refCB: React.MutableRefObject<{
    setDate: TSStateSetCB<Date>, stateEvents: TSEventsView, setEvents: TSStateSetCB<TSEventsView>, setCalc: TSStateSetCB<boolean>,
                          }>, stateDate: Date, stateView: string, stateSymbol: string, stateAggregate: boolean,
                          ticks: TSChartTicks, stateMessages: TSLogMessage[]) {
    return (events?: TSTradeMessage[]) => {
        refCB.current.setCalc(true) // overlap stateFetch and stateCalc (early stateCalc)

        if (events && isSymbolType(stateSymbol, events[0].type)
            && ticks[0].value <= stateDate.getTime() && events[0].date > ticks.slice(-1)[0].value) {
            //refCB.current.setDate(new Date())
            const date = new Date(stateDate)
            if (LV.datePlusTick(date, stateView)) refCB.current.setDate(date)
        }

        //refCB.current.setEvents(stateEvents => {
        const newState = processEvents(
            events ? events : stateMessages.filter(msg => !('data' in msg)) as TSTradeMessage[],
            stateView, stateSymbol, stateAggregate,
            ticks[0].value, ticks.slice(-1)[0].value,
            events ? refCB.current.stateEvents : undefined)
        //    return newState ?? stateEvents
        //})
        if (newState) refCB.current.setEvents(newState)
    }
}


//---

export function useLiveTradesEvents(stateDate: Date, setDate: TSStateSetCB<Date>,
                                    stateView: string, stateSymbol: string, stateAggregate: boolean,
                                    ticks: TSChartTicks) {
    const [stateMessages, setMessages] = React.useState<TSLogMessage[]>([])
    const [stateEvents, setEvents] = React.useState<TSEventsView>(LV.EventsInit)
    const [stateCalc, setCalc] = React.useState(false)
    const [statePending, setPending] = React.useState(0)

    const cbGetMessages = React.useCallback(getMessages, [])

    const cbOnMessage = React.useCallback(onMessageCB(setMessages), [])

    const cbOnClear = React.useCallback(() => {
        setMessages([])
        setEvents(LV.EventsInit)
    }, [])

    const refCBCurrent = {setDate, stateEvents, setEvents, setCalc}
    const refCB = React.useRef(refCBCurrent)
    refCB.current = refCBCurrent

    const cbOnEvent = React.useCallback(onEventCB(refCB, stateDate, stateView, stateSymbol, stateAggregate, ticks, stateMessages),
        [stateDate, stateView, stateSymbol, stateAggregate, ticks, stateMessages])

    React.useLayoutEffect(() => {
        cbOnEvent()
    }, [statePending, stateView, stateSymbol, stateAggregate, ticks])

    const cbOnEvents = React.useCallback(() => setPending(state => state + 1),
        [])

    return {
        stateMessages, setMessages, getMessages: cbGetMessages, onMessage: cbOnMessage, onClear: cbOnClear,
        stateEvents, onEvent: cbOnEvent, onEvents: cbOnEvents, stateCalc, setCalc,
    }
}
