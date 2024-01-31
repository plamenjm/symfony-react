import React from 'react';
import {Align, CategoryScale, Chart, ChartData, Colors, Legend, LinearScale, LineElement, LogarithmicScale, PointElement, Title, Tooltip, TooltipItem} from 'chart.js';
import {ChartJSOrUndefined} from 'react-chartjs-2/dist/types';
import {LV, TSChartTicks, TSEventsView, TSTradeMessage} from '/assets/react/modules/utilsLiveTrades';
import {TSStateSetCB} from '/assets/Utils';


//---

Chart.register(CategoryScale, Colors, LinearScale, LogarithmicScale, Legend, LineElement, PointElement, Title, Tooltip)


//---

export function onAnimationCB(
    setCalc: TSStateSetCB<boolean>,
) {
    return (event: AnimationEvent) => setCalc(false)
}

export function onTooltipTitleCB(refCB: React.MutableRefObject<{
    stateView: string,
}>) {
    return (items: TooltipItem<'line'>[]) => {
        const raw = items?.[0]?.raw as {x: number, amount: number}
        return (!raw?.x) ? '' : LV.chartTooltip(new Date(raw.x), refCB.current.stateView)
    }
}

export function onTooltipLabelCB() {
    return (items: TooltipItem<'line'>[]) => {
        const item = (items as unknown as TooltipItem<'line'>) //? bug in react-chartjs-2
        const raw = item?.raw as {y: number, amount: number}
        return (!raw?.y) ? '' : ' ' + item.dataset.label
            + ' ' + raw.y.toLocaleString(undefined, {minimumFractionDigits: 3})
            + '  x  ' + raw.amount.toLocaleString(undefined, {maximumFractionDigits: 9})
    }
}

export function onTicksY(value: number, idx: number, ticks: TSChartTicks) {
    if (idx === 0 || idx === ticks.length - 1) return ''

    return value.toLocaleString(undefined, {minimumFractionDigits: 3})
}

export function onTicksX(value: number, idx: number, ticks: TSChartTicks) {
    //if (idx === ticks.length - 1) console.log('TICKS', ticks
    //    .filter((o, idx) => idx === 0 || idx === ticks.length - 1)
    //    .map(({value}) => (new Date(value)).toISOString()))
    //return (new Date(value)).toISOString().slice(5, 10) // '2024-01-15T07:44:22.301Z' = '01-15'

    return ticks[idx].label
}

export function getChartOptionsData(title: string, titleY: string, minY: number, maxY: number, axis: string,
                                    ticks: TSChartTicks, data: TSTradeMessage[], label: string,
                                    onTooltipTitle: (item: TooltipItem<'line'>[]) => string,
                                    onTooltipLabel: (item: TooltipItem<'line'>[]) => string,
                                    onAnimation: (event: AnimationEvent) => void,
): {options: object, data: ChartData<'line'>} { //options: ChartOptions<'line'>
    return {
        options: {
            //responsive: false,
            animations: {
                x: {duration: 0},
                y: {duration: 0},
                borderWidth: {duration: 0},
                radius: {duration: 0},
                tension: {loop: true, duration: 1000, easing: 'linear', from: 0.11, to: 0.34},
            },
            animation: {duration: 0, onProgress: onAnimation, onComplete: onAnimation},
            plugins: {
                colors: {forceOverride: true},
                legend: {display: false}, //position: 'bottom' as LayoutPosition, fullSize: false, align: 'start' as const,
                title: {
                    text: title, display: true, fullSize: false, align: 'start' as Align, //position: 'left' as const
                    font: {size: 14},
                },
                tooltip: {callbacks: {
                        title: onTooltipTitle,
                        label: onTooltipLabel,
                    }},
            },
            scales: {
                y: {
                    type: axis, min: minY, max: maxY,
                    title: {text: titleY, display: true, font: {weight: 'bold'}},
                    ticks: {callback: onTicksY},
                },
                x: {
                    type: LV.EnumAxis.Line, min: ticks[0].value, max: ticks.slice(-1)[0].value,
                    ticks: {callback: onTicksX},
                    afterBuildTicks: (axis: {ticks: object[]}) => axis.ticks = ticks,
                },
            },
            //parsing: false, normalized: true,
        },
        data: {
            //labels: data.map(({date}) => date), // default axis X: scales.x.type: 'category'
            datasets: [{
                label, borderWidth: 1, tension: 0.34,
                //data: data.map(({price}) => price) // default axis X: scales.x.type: 'category'
                data: data.map(({date: x, price: y, amount}) => ({x, y, amount})),
            }],
        },
    }
}


//---

export function useLiveTradesChart(stateDate: Date, stateView: string, stateSymbol: string, stateAxis: string,
                                   ticks: TSChartTicks, stateEvents: TSEventsView,
                                   setCalc: TSStateSetCB<boolean>) {
    const refChart = React.useRef<ChartJSOrUndefined<'line'>>(null)

    const refCBValue = {stateView}
    const refCB = React.useRef(refCBValue)
    refCB.current = refCBValue

    const memoChart = React.useMemo(() => {
        setCalc(true) // overlap stateFetch and stateCalc

        //const title = stateDate.toISOString().slice(0, 10)
        const title = stateDate.toISOString().split(/[T.]/).slice(0, 2).join(' ')
            + ' / events: ' + stateEvents.data.length

        const titleY = 'Bitcoin price' + (stateAxis === LV.EnumAxis.Line ? ' in ' : ' Log in ')
            + (stateSymbol !== LV.EnumSymbol.USD ? '' : 'US Dollars')
            + (stateSymbol !== LV.EnumSymbol.EUR ? '' : 'EU Euro')

        const minY = 2 * stateEvents.min - stateEvents.avg, maxY = 2 * stateEvents.max - stateEvents.avg

        const label = (stateSymbol !== LV.EnumSymbol.USD ? '' : 'USD') + (stateSymbol !== LV.EnumSymbol.EUR ? '' : 'EUR')

        return getChartOptionsData(title, titleY, minY, maxY, stateAxis, ticks, stateEvents.data, label,
            onTooltipTitleCB(refCB), onTooltipLabelCB(), onAnimationCB(setCalc))
    }, [stateDate, stateSymbol, stateAxis, ticks, stateEvents.data]) //stateEvents

    return {refChart, ...memoChart}
}
