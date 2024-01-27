import React, {useRef} from 'react';
import {ChartData} from 'chart.js';
import {Line as ChartLine} from 'react-chartjs-2';
import {ChartJSOrUndefined} from 'react-chartjs-2/dist/types';
import {Config} from '/assets/Config';
import {Utils} from '/assets/Utils';
import {Jsx} from '/assets/react/Jsx';
import {LV} from '/assets/react/modules/utilsLiveTrades';
import {useLiveTrades} from '/assets/react/modules/hookLiveTrades';
import {useLiveTradesEvents} from '/assets/react/modules/hookLiveTradesEvents';
import {useLiveTradesLive} from '/assets/react/modules/hookLiveTradesLive';
import {useLiveTradesLog} from '/assets/react/modules/hookLiveTradesLog';
import {useLiveTradesChart} from '/assets/react/modules/hookLiveTradesChart';
import '/assets/react/Page.css';

const MemoChartLine = React.memo(
    React.forwardRef<ChartJSOrUndefined<'line'>, {options: object, data: ChartData<'line'>}>(
    (props, ref) => {
        ref = ref as React.RefObject<ChartJSOrUndefined<'line'>>
        if (Config.DevLogEnable) {
            const refRender = useRef(0)
            Utils.log('Render Chart', ++refRender.current, props.data.datasets[0].data.length) //ref.current?.data.datasets?.[0].data.length
        }

        return <ChartLine ref={ref} options={props.options} data={props.data}/>
    }
))

export function PageLiveTrades() {
    const {stateDate, setDate, onPrev, onNext, stateView, radioView, stateSymbol, radioSymbol, stateAxis, radioAxis, stateAggregate, check, ticks}
        = useLiveTrades()
    const {stateMessages, setMessages, getMessages, onMessage, onClear, stateEvents, onEvent, onEvents, stateCalc, setCalc}
        = useLiveTradesEvents(stateDate, setDate, stateView, stateSymbol, stateAggregate, ticks)

    const {stateUrl, setUrl, onWSMessage, stateFetch} =
        useLiveTradesLog(stateDate, stateView, stateSymbol, ticks, setMessages, onMessage, onClear, onEvent, onEvents)
    const {onConnect, onDisconnect, isReconnect} =
        useLiveTradesLive(stateUrl, setUrl, onWSMessage, setMessages, onMessage, onClear, stateMessages.length === 0, onEvents)

    const {refChart, options, data}
        = useLiveTradesChart(stateDate, stateView, stateSymbol, stateAxis, ticks, stateEvents, setCalc)

    if (Config.DevLogEnable) {
        const refRender = useRef(0)
        Utils.log('Render Page', ++refRender.current, stateEvents.data.length, stateMessages.length)
    }
    if (Config.DevLogEnable) React.useLayoutEffect(() => {
        console.log(stateFetch, stateCalc, stateFetch || stateCalc)
    }, [stateFetch, stateCalc])

    const loading = stateFetch || stateCalc
    const reconnect = !stateUrl && !isReconnect

    return (
        <div className='pageContent'>
            <div style={{display: 'flex'}}>
                <div>
                    <button disabled={loading || !reconnect} onClick={onConnect}>Connect</button>
                    <br/>
                    <button disabled={loading || reconnect} onClick={onDisconnect}>Disconnect</button>
                    <br/>
                    <button onClick={onClear}>Clear</button>
                    {/*<br/>
                    <button onClick={() => refChart.current?.update()}>Test Chart API</button>*/}
                </div>
                &nbsp;&nbsp;
                <div>
                    <button disabled={loading} onClick={onPrev}>{'<'}</button>
                    <br/>
                    <button disabled={loading} onClick={onNext}>{'>'}</button>
                </div>
                &nbsp;&nbsp;
                <div>
                    <input disabled={loading} {...radioView(LV.EnumView.Hour)}/>
                    <label {...Jsx.radioLbl(...LV.RBViewHour)}>Hour view</label>
                    <br/>
                    <input disabled={loading} {...radioView(LV.EnumView.Day)}/>
                    <label {...Jsx.radioLbl(...LV.RBViewDay)}>Day view</label>
                    <br/>
                    <input disabled={loading} {...radioView(LV.EnumView.Week)}/>
                    <label {...Jsx.radioLbl(...LV.RBViewWeek)}>Week view</label>
                </div>
                &nbsp;&nbsp;
                <div>
                    <input disabled={loading} {...radioSymbol(LV.EnumSymbol.USD)}/>
                    <label {...Jsx.radioLbl(...LV.RBSymbolUSD)}>USD</label>
                    <br/>
                    <input disabled={loading} {...radioSymbol(LV.EnumSymbol.EUR)}/>
                    <label {...Jsx.radioLbl(...LV.RBSymbolEUR)}>EUR</label>
                </div>
                &nbsp;&nbsp;
                <div>
                    <input {...radioAxis(LV.EnumAxis.Line)}/>
                    <label {...Jsx.radioLbl(...LV.RBAxisLine)}>Linear</label>
                    <br/>
                    <input {...radioAxis(LV.EnumAxis.Log)}/>
                    <label {...Jsx.radioLbl(...LV.RBAxisLog)}>Logarithmic</label>
                    <br/>
                    <input disabled={loading} {...check('agg', stateAggregate)}/>
                    <label htmlFor='agg'>Aggregate</label>
                </div>
                &nbsp;&nbsp;
                <div>
                    <label htmlFor='email'>Email </label><input type='text' placeholder='Email'/>
                    <br/>
                    <label htmlFor='price'>Price </label><input type='text' placeholder='Price'/>
                </div>
                &nbsp;&nbsp;
                <div>
                    <label htmlFor='percent'>Percent </label><input type='number' placeholder='%'/>
                    <br/>
                    <label htmlFor='interval'>Interval </label>
                    <select>
                        <option value='1'>1 hour</option>
                        <option value='6'>6 hours</option>
                        <option value='24'>24 hours</option>
                    </select>
                </div>
            </div>

            <MemoChartLine ref={refChart} options={options} data={data}/>

            <pre>
                {getMessages(stateMessages, false).map(msg => <React.Fragment key={msg.idx}>
                    {'data' in msg ? msg.data : JSON.stringify({...msg, date: Utils.dateTimeUTC(msg.date)})}
                    <br/>
                </React.Fragment>)}
            </pre>
        </div>
    )
}
