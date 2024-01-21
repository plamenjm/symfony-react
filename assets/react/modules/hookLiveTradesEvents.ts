import React from 'react';
import {Config} from '/assets/Config';
import {LV, TSChartTicks, TSEventsView, TSLogMessage, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';
import {TSStateSetCB, Utils} from '/assets/Utils';


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
        const ev = res.length && {...res[res.length - 1]}
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
                              view: string, symbol: string, from: number, to: number,
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
        data: Config.LiveTradesAggregateEvents ? eventsAggregate(eventsView, view) : eventsView}
}


//---

export function useLiveTradesEvents(stateDate: Date, setDate: TSStateSetCB<Date>, stateView: string, stateSymbol: string, ticks: TSChartTicks) {
    const [stateMessages, setMessages] = React.useState<TSLogMessage[]>([])
    const [stateEvents, setEvents] = React.useState<TSEventsView>(LV.EventsInit)
    const [statePending, setPending] = React.useState(0)

    const getMessages = (keepState?: TSLogMessage[]) => {
        //const keepMessages = 1000 // test
        const state = keepState ? keepState : stateMessages
        return [
            ...state.slice(0, keepMessages),
            ...(!keepState ? [{idx: -1, data: '...'}] : state.slice(keepMessages, -keepMessages).filter(msg => !('data' in msg))),
            ...(state.length - keepMessages <= 0 ? [] : state.slice(-Math.min(keepMessages, state.length - keepMessages))),
        ]
    }

    function onMessage(message: string | TSLogMessage) {
        const msg = typeof message === 'object' ? message : {data: Utils.dateTimeUTC() + ' ' + message}
        setMessages(state => [{...msg, idx: 1 + (state[0]?.idx ?? 0)}, ...getMessages(state)])
    }

    function onClear() {
        setMessages([])
        setEvents(LV.EventsInit)
    }

    function onEvent(events?: TSTradeMessage[]) {
        if (events && isSymbolType(stateSymbol, events[0].type)
            && events[0].date > ticks[ticks.length - 1].value) {
            const date = new Date()
            if (LV.datePlusTick(date, stateView)) setDate(date)
        }

        //setEvents(stateEvents => {
            const newState = processEvents(events ? events
                    : stateMessages.filter(msg => !('data' in msg)) as TSTradeMessage[],
                stateView, stateSymbol, ticks[0].value, ticks[ticks.length - 1].value,
                events ? stateEvents : undefined)
        //    return newState ?? stateEvents
        //})
        if (newState) setEvents(newState)
    }

    React.useLayoutEffect(() => {
        onEvent()
    }, [statePending, stateView, stateSymbol, ticks])

    const onEvents = () => setPending(state => state + 1)

    return {stateMessages, setMessages, getMessages, onMessage, onClear, stateEvents, onEvent, onEvents}
}
