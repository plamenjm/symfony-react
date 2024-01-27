import {Config} from '/assets/Config';


//---

export type TSRawMessages = string[]

export type TSTradeMessage = {idx?: number, id?: string, type: string, date: number, price: number, amount: number}
export type TSInfoMessage = {idx?: number, data: string}
export type TSLogMessage = TSInfoMessage | TSTradeMessage

export type TSEventsView = {min: number, max: number, avg: number, count: number, data: TSTradeMessage[]} //data: number[], labels: number[]
export type TSChartTicks = Array<{value: number, label: string}>


//---

const lv = {
    EnumView: {Hour: 'hour', Day: 'day', Week: 'week'},
    RBView: 'view',

    EnumSymbol: {USD: 'usd', EUR: 'eur'},
    RBSymbol: 'symbol',

    EnumAxis: {Line: 'linear', Log: 'logarithmic'},
    RBAxis: 'axis',
}

export const LV = Object.freeze({
    ...lv,

    EnumEvent: {USD: 'tBTCUSD', EUR: 'tBTCEUR'},
    EventsInit: {min: 0, max: 0, avg: 0, count: 0, data: []},

    ChartTicks: {Hour: 12, Day: 24, Week: 7},

    RBViewHour: [lv.RBView, lv.EnumView.Hour] as [string, string],
    RBViewDay : [lv.RBView, lv.EnumView.Day] as [string, string],
    RBViewWeek: [lv.RBView, lv.EnumView.Week] as [string, string],

    RBSymbolUSD: [lv.RBSymbol, lv.EnumSymbol.USD] as [string, string],
    RBSymbolEUR: [lv.RBSymbol, lv.EnumSymbol.EUR] as [string, string],

    RBAxisLine: [lv.RBAxis, lv.EnumAxis.Line] as [string, string],
    RBAxisLog : [lv.RBAxis, lv.EnumAxis.Log] as [string, string],

    datePlusTick: function(date: Date, view: string) {
        if (view === LV.EnumView.Hour) date.setMinutes(date.getMinutes() + 60 / LV.ChartTicks.Hour)
        else if (view === LV.EnumView.Day) date.setHours(date.getHours() + 24 / LV.ChartTicks.Day)
        else if (view === LV.EnumView.Week) date.setDate(date.getDate() + 7 / LV.ChartTicks.Week)
        else return false
        return true
    },

    chartTooltip: function(date: Date, view: string): string {
        const label = date.toISOString()                                // 2024-01-15T07:44:22.301Z
        return label.split('T').join(' ').slice(0, 19)         // 2024-01-15 07:44:22
        //if (view === LV.EnumView.Hour) return label.slice(11, 19)       // 07:44:22
        //if (view === LV.EnumView.Day) return +label.slice(11, 16)       // 07:44
        //if (view === LV.EnumView.Week) return label.slice(0, 10)        // 2025-01-15
        //return ''
    },

    chartTickLabel: function(date: Date, view: string): string {
        const label = date.toISOString()                                // 2024-01-15T07:44:22.301Z
        if (view === LV.EnumView.Hour) return label.slice(11, 16)       // 07:44
        if (view === LV.EnumView.Day) return +label.slice(11, 13) + 'h' // 07h
        if (view === LV.EnumView.Week) return label.slice(5, 10)        // 01-15
        return ''
    },
})
