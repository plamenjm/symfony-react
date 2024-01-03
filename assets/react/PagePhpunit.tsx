import React, { useCallback, useState } from "react";
import { Constants } from '/assets/Utils';
import { fetchApi, FetchApiPhpunit } from '/assets/FetchApi';
import '/assets/react/Page.css';

interface StateApiPhpunit extends FetchApiPhpunit {loading?: boolean}

export function PagePhpunit() {
    const resource = 'phpunit'
    const [stateFetch, setFetch] = useState<StateApiPhpunit>({})

    const onClick = useCallback(() => {
        setFetch({loading: true})
        fetchApi(resource).then(({error, json}) =>
            setFetch({loading: false, json,
                error: error ?? (json?.processStdOut ? undefined : Constants.ErrorUnexpected)})
        )
    }, [])

    const processError = stateFetch.json && stateFetch.json.processExitCode
    const processStdOut = !stateFetch.json ? undefined : '$ ' + stateFetch.json.process + '\n'
        + (!stateFetch.json.processStdErr ? '' : '\nSTDERR:\n' + stateFetch.json.processStdErr + '\nSTDOUT:\n')
        + stateFetch.json.processStdOut

    return (
        <div className='pageContent'>
            <button onClick={onClick} disabled={stateFetch.loading}>api/{resource}</button>
            <pre className={processError ? 'error' : undefined}>
                {stateFetch.loading ? 'loading...'
                    : stateFetch.error ? <div className='pageError'>{stateFetch.error}</div>
                        : processStdOut}
            </pre>
        </div>
    )
}
