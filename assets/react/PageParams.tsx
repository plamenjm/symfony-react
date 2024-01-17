import React, {useCallback, useState} from 'react';
import {Constant} from '/assets/Constant';
import {fetchApi, FetchApiParams} from '/assets/FetchApi';
import '/assets/react/Page.css';

interface StateApiParams extends FetchApiParams {loading?: boolean}

export function PageParams() {
    const resource = 'params'
    const [stateFetch, setFetch] = useState<StateApiParams>({})

    const onClick = useCallback(() => {
        setFetch({loading: true})
        fetchApi(resource).then(({error, json}) =>
            setFetch({loading: false, json,
                error: error ?? (json?.testHappyMessage ? undefined : Constant.ErrorUnexpected)})
        )
    }, [])

    return (
        <div className='pageContent'>
            <button onClick={onClick} disabled={stateFetch.loading}>api/{resource}</button>
            <pre>
                {stateFetch.loading ? 'loading...'
                    : stateFetch.error ? <div className='pageError'>{stateFetch.error}</div>
                        : JSON.stringify(stateFetch.json)}
            </pre>
        </div>
    )
}
