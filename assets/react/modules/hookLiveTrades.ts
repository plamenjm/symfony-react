import React from 'react';
import {Jsx} from '/assets/react/Jsx';
import {LV, TSChartTicks} from '/assets/react/modules/utilsLiveTrades';


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


//---

export function useLiveTrades() {
    const [stateDate, setDate] = React.useState(new Date())
    const [stateView, setView] = React.useState(LV.EnumView.Day)
    const [stateSymbol, setSymbol] = React.useState(LV.EnumSymbol.USD)
    const [stateAxis, setAxis] = React.useState(LV.EnumAxis.Line)

    const memoTicks = React.useMemo(() => {
        return stateView === LV.EnumView.Hour ? chartTicksHour(stateDate)
            : stateView === LV.EnumView.Day ? chartTicksDay(stateDate)
                : stateView === LV.EnumView.Week ? chartTicksWeek(stateDate)
                    : []
    }, [stateDate, stateView])

    function onDate(direction: 1 | -1) {
        const date = new Date(stateDate)
        if (stateView === LV.EnumView.Hour)
            date.setHours(date.getHours() + direction)
        else {
            const days = stateView === LV.EnumView.Day ? 1
                : (stateView === LV.EnumView.Week ? 7
                    : 0)
            date.setDate(date.getDate() + direction * days)
        }
        setDate(date)
    }

    const onPrev = () => onDate(-1)
    const onNext = () => onDate(1)
    const radioView = (value: string) => Jsx.radio(LV.RBView, value, stateView, (state) => {
        //setDate(new Date())
        setView(state)
    })
    const radioSymbol = (value: string) => Jsx.radio(LV.RBSymbol, value, stateSymbol, setSymbol)
    const radioAxis = (value: string) => Jsx.radio(LV.RBAxis, value, stateAxis, setAxis)

    return {stateDate, setDate, onPrev, onNext, stateView, radioView, stateSymbol, radioSymbol, stateAxis, radioAxis, ticks: memoTicks}
}
