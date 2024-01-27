import React from 'react';
import {Jsx} from '/assets/react/Jsx';
import {LV, TSChartTicks} from '/assets/react/modules/utilsLiveTrades';
import {Config} from '/assets/Config';


//---

export function chartTicksHour(forDate: Date): TSChartTicks {
    const date = new Date(forDate)
    date.setMinutes(Math.trunc(date.getMinutes() / 10) * 10 - 60, 0, 0)
    const ticks = Array.from(Array(LV.ChartTicks.Hour + 1))
    ticks.forEach((u, idx) => {
        LV.datePlusTick(date, LV.EnumView.Hour)
        ticks[idx] = {value: date.getTime(), label: LV.chartTickLabel(date, LV.EnumView.Hour)}
    })
    //console.log('ticks', ticks
    //    .filter((o, idx) => idx === 0 || idx === ticks.length - 1)
    //    .map(({value}) => (new Date(value)).toISOString()))
    return ticks
}

export function chartTicksDay(forDate: Date): TSChartTicks {
    const date = new Date(forDate)
    date.setHours(date.getHours() - 24, 0, 0, 0)
    const ticks = Array.from(Array(LV.ChartTicks.Day + 1))
    ticks.forEach((u, idx) => {
        LV.datePlusTick(date, LV.EnumView.Day)
        ticks[idx] = {value: date.getTime(), label: LV.chartTickLabel(date, LV.EnumView.Day)}
    })
    //console.log('ticks', ticks
    //    .filter((o, idx) => idx === 0 || idx === ticks.length - 1)
    //    .map(({value}) => (new Date(value)).toISOString()))
    return ticks
}

export function chartTicksWeek(forDate: Date): TSChartTicks {
    const date = new Date(forDate)
    date.setUTCHours(0, 0, 0, 0)
    date.setDate(date.getDate() - 7)
    const ticks = Array.from(Array(LV.ChartTicks.Week + 1))
    ticks.forEach((u, idx) => {
        LV.datePlusTick(date, LV.EnumView.Week)
        ticks[idx] = {value: date.getTime(), label: LV.chartTickLabel(date, LV.EnumView.Week)}
    })
    //console.log('ticks', ticks
    //    .filter((o, idx) => idx === 0 || idx === ticks.length - 1)
    //    .map(({value}) => (new Date(value)).toISOString()))
    return ticks
}

function changeDate(direction: 1 | -1, date: Date, view: string) {
    const change = new Date(date)
    if (view === LV.EnumView.Hour)
        change.setHours(change.getHours() + direction)
    else {
        const days = view === LV.EnumView.Day ? 1
            : (view === LV.EnumView.Week ? 7
                : 0)
        change.setDate(change.getDate() + direction * days)
    }
    return change
}


//---

export function useLiveTrades() {
    const [stateDate, setDate] = React.useState(new Date())
    const [stateView, setView] = React.useState(LV.EnumView.Day)
    const [stateSymbol, setSymbol] = React.useState(LV.EnumSymbol.USD)
    const [stateAxis, setAxis] = React.useState(LV.EnumAxis.Line)
    const [stateAggregate, setAggregate] = React.useState<boolean>(Config.LiveTradesAggregateEvents)

    const refCBValue = {stateDate, stateView, stateSymbol, stateAxis}
    const refCB = React.useRef(refCBValue)
    refCB.current = refCBValue

    const cbOnPrev = React.useCallback(() =>
            setDate(changeDate(-1, refCB.current.stateDate, refCB.current.stateView)),
        [])
    const cbOnNext = React.useCallback(() =>
            setDate(changeDate(1, refCB.current.stateDate, refCB.current.stateView)),
        [])

    const cbRadioView = React.useCallback((value: string) =>
        Jsx.radio(LV.RBView, value, refCB.current.stateView, (value) => {
            setDate(new Date())
            setView(value)
        }), [])

    const cbRadioSymbol = React.useCallback((value: string) =>
        Jsx.radio(LV.RBSymbol, value, refCB.current.stateSymbol, (value) => {
            setDate(new Date())
            setSymbol(value)
        }), [])

    const cbRadioAxis = React.useCallback((value: string) =>
        Jsx.radio(LV.RBAxis, value, refCB.current.stateAxis, (value) => {
            setDate(new Date())
            setAxis(value)
        }), [])

    const cbCheck = React.useCallback((id: string, state: boolean) =>
            Jsx.check(id, state, () => setAggregate(!state)),
        [])

    const memoTicks = React.useMemo(() => {
        if (stateView === LV.EnumView.Hour) return chartTicksHour(refCB.current.stateDate)
        if (stateView === LV.EnumView.Day) return chartTicksDay(refCB.current.stateDate)
        if (stateView === LV.EnumView.Week) return chartTicksWeek(refCB.current.stateDate)
        return []
    }, [stateDate, stateView])

    return {
        stateDate, setDate, onPrev: cbOnPrev, onNext: cbOnNext,
        stateView, radioView: cbRadioView,
        stateSymbol, radioSymbol: cbRadioSymbol,
        stateAxis, radioAxis: cbRadioAxis,
        stateAggregate, check: cbCheck,
        ticks: memoTicks,
    }
}
