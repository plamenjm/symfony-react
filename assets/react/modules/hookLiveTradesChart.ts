import React from 'react';
import {Align, CategoryScale, Chart, ChartData, Colors, Legend, LinearScale, LineElement, LogarithmicScale, PointElement, Title, Tooltip, TooltipItem} from 'chart.js';
import {ChartJSOrUndefined} from 'react-chartjs-2/dist/types';
import {LV, TSChartTicks, TSEventsView} from '/assets/react/modules/utilsLiveTrades';


//---

Chart.register(CategoryScale, Colors, LinearScale, LogarithmicScale, Legend, LineElement, PointElement, Title, Tooltip)


//---

export function chartTooltipTitleCB(item: TooltipItem<'line'>[], view: string) {
    const raw = item?.[0]?.raw as {x: number}
    return (!raw?.x) ? '' : LV.chartTooltip(new Date(raw.x), view)
}

export function chartTicksCB(value: number, idx: number, ticks: TSChartTicks) {
    //if (idx === ticks.length - 1) console.log('TICKS', ticks
    //    .filter((o, idx) => idx === 0 || idx === ticks.length - 1)
    //    .map(({value}) => (new Date(value)).toISOString()))

    //return (new Date(value)).toISOString().slice(5, 10) // '2024-01-15T07:44:22.301Z' = '01-15'
    return ticks[idx].label
}

export function getChartOptionsData(title: string, titleY: string, view: string, symbol: string, axis: string,
                                    ticks: TSChartTicks, events: TSEventsView, //, label: string,
): {options: object, data: ChartData<'line'>} { //options: ChartOptions<'line'>
    const min = 2 * events.min - events.avg, max = 2 * events.max - events.avg
    return {
        options: {
            //responsive: false,
            plugins: {
                colors: {forceOverride: true},
                legend: {display: false}, //position: 'bottom' as LayoutPosition, fullSize: false, align: 'start' as const,
                title: {
                    text: title, display: true, fullSize: false, align: 'start' as Align, //position: 'left' as const
                    font: {size: 14},
                },
                tooltip: {callbacks: {title: (item: TooltipItem<'line'>[]) => chartTooltipTitleCB(item, view)}},
            },
            scales: {
                y: {
                    type: axis, min, max,
                    title: {text: titleY, display: true, font: {weight: 'bold'}},
                    ticks: {
                        callback: (value: number, idx: number, ticks: TSChartTicks) =>
                            idx === 0 || idx === ticks.length - 1 ? ''
                                : value.toLocaleString(undefined, {minimumFractionDigits: 3}),
                    },
                },
                x: {
                    type: LV.EnumAxis.Line, min: ticks[0].value, max: ticks[ticks.length - 1].value,
                    ticks: {
                        callback: (value: number, idx: number, ticks: TSChartTicks) =>
                            //idx === ticks.length - 1 ? '' :
                                chartTicksCB(value, idx, ticks),
                    },
                    afterBuildTicks: (axis: {ticks: object[]}) => axis.ticks = ticks,
                },
            },
            //parsing: false, normalized: true,
        },
        data: {
            //labels: events.data.map(({date}) => date), // default axis X: scales.x.type: 'category'
            datasets: [{
                tension: 0.34, //label,
                //data: events.data.map(({price}) => price) // default axis X: scales.x.type: 'category'
                data: events.data.map(({date: x, price: y}) => ({x, y})),
            }],
        },
    }
}

export function useLiveTradesChart(stateEvents: TSEventsView,
                                   stateDate: Date, stateView: string, stateSymbol: string, stateAxis: string,
                                   ticks: TSChartTicks) {
    //const refChart = React.useRef<Chart<'line'>>(null)
    const refChart = React.useRef<ChartJSOrUndefined<'line'>>(null)

    const {options, data} = React.useMemo(() => {
        //const title = stateDate.toISOString().slice(0, 10)
        const title = stateDate.toISOString().split(/[T.]/).slice(0, 2).join(' ')

        const titleY = 'Bitcoin price' + (stateAxis === LV.EnumAxis.Line ? ' in ' : ' Log in ')
            + (stateSymbol !== LV.EnumSymbol.USD ? '' : 'US Dollars')
            + (stateSymbol !== LV.EnumSymbol.EUR ? '' : 'EU Euro')

        //const label = stateSymbol === RBSymbol.USD ? 'USD' : (stateSymbol === RBSymbol.EUR ? 'EUR' : 'null')

        return getChartOptionsData(title, titleY, stateView, stateSymbol, stateAxis, ticks, stateEvents) //label
    }, [stateEvents, stateDate, stateView, stateSymbol, stateAxis])

    return {refChart, options, data}
}
