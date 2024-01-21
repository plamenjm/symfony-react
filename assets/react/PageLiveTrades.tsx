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
import {useLiveTradesWebSocket} from '/assets/react/modules/hookLiveTradesWebSocket';
import {useLiveTradesMessages} from '/assets/react/modules/hookLiveTradesMessages';
import {useLiveTradesChart} from '/assets/react/modules/hookLiveTradesChart';
import '/assets/react/Page.css';

const MemoChartLine = React.memo(
    React.forwardRef<ChartJSOrUndefined<'line'>, {options: object, data: ChartData<'line'>}>(
    (props, ref) => {
        ref = ref as React.RefObject<ChartJSOrUndefined<'line'>>
        if (Config.DevLogEnable) {
            const refRender = useRef(0)
            Utils.log('render', ++refRender.current, props.data.datasets[0].data.length) //ref.current?.data.datasets?.[0].data.length
        }

        return <ChartLine ref={ref} options={props.options} data={props.data}/>
    }
))

export function PageLiveTrades() {
    const {stateDate, setDate, onPrev, onNext, stateView, radioView, stateSymbol, radioSymbol, stateAxis, radioAxis, ticks}
        = useLiveTrades()
    const {stateMessages, setMessages, getMessages, onMessage, onClear, stateEvents, onEvent, onEvents}
        = useLiveTradesEvents(stateDate, setDate, stateView, stateSymbol, ticks)
    const {stateUrl, onConnect, onDisconnect, reconnect, lastMessage, lastJsonMessage}
        = useLiveTradesWebSocket(stateMessages.length === 0, setMessages, onMessage, onClear, onEvents)
    useLiveTradesMessages(lastMessage, lastJsonMessage, setMessages, onMessage, onEvent, onEvents)
    const {refChart, options, data}
        = useLiveTradesChart(stateEvents, stateDate, stateView, stateSymbol, stateAxis, ticks)

    if (Config.DevLogEnable) {
        const refRender = useRef(0)
        Utils.log('render', ++refRender.current, stateEvents.data.length, stateMessages.length)
    }

    return (
        <div className='pageContent'>
            <div style={{display: 'flex'}}>
                <div>
                    <button disabled={!!stateUrl || reconnect} onClick={onConnect}>Connect</button>
                    <br/>
                    <button disabled={!stateUrl && !reconnect} onClick={onDisconnect}>Disconnect</button>
                    <br/>
                    <button onClick={onClear}>Clear</button>
                    {/*<br/>
                    <button onClick={() => refChart.current?.update()}>Test Chart API</button>*/}
                </div>
                &nbsp;&nbsp;
                <div>
                    <button onClick={onPrev}>{'<'}</button>
                    <br/>
                    <button onClick={onNext}>{'>'}</button>
                </div>
                &nbsp;&nbsp;
                <div>
                    <label {...Jsx.radioLbl(...LV.RBViewHour)}>Hour view</label><input {...radioView(LV.EnumView.Hour)}/>
                    <br/>
                    <label {...Jsx.radioLbl(...LV.RBViewDay)}>Day view</label><input {...radioView(LV.EnumView.Day)}/>
                    <br/>
                    <label {...Jsx.radioLbl(...LV.RBViewWeek)}>Week view</label><input {...radioView(LV.EnumView.Week)}/>
                </div>
                &nbsp;&nbsp;
                <div>
                    <label {...Jsx.radioLbl(...LV.RBSymbolUSD)}>USD</label><input {...radioSymbol(LV.EnumSymbol.USD)}/>
                    <br/>
                    <label {...Jsx.radioLbl(...LV.RBSymbolEUR)}>EUR</label><input {...radioSymbol(LV.EnumSymbol.EUR)}/>
                </div>
                &nbsp;&nbsp;
                <div>
                    <label {...Jsx.radioLbl(...LV.RBAxisLine)}>Linear</label><input {...radioAxis(LV.EnumAxis.Line)}/>
                    <br/>
                    <label {...Jsx.radioLbl(...LV.RBAxisLog)}>Logarithmic</label><input {...radioAxis(LV.EnumAxis.Log)}/>
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
                {getMessages().map(msg => <React.Fragment key={msg.idx}>
                    {'data' in msg ? msg.data : JSON.stringify({...msg, date: Utils.dateTimeUTC(msg.date)})}
                    <br/>
                </React.Fragment>)}
            </pre>
        </div>
    )
}
