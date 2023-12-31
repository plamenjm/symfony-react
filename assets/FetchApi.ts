import { Config } from '/assets/Utils';

type ModelApiParams = {test: string, happyMessage: string}
type ModelApiPhpunit = {process: string, processOutput: string, processExitCode: string}

//---

type FetchApi = {error?: string, json?: object}
export interface FetchApiParams extends FetchApi {error?: string, json?: ModelApiParams}
export interface FetchApiPhpunit extends FetchApi {error?: string, json?: ModelApiPhpunit}

//---

type FetchResource = 'params' | 'phpunit'
export function fetchApi(resource: 'params'): Promise<FetchApiParams>
export function fetchApi(resource: 'phpunit'): Promise<FetchApiPhpunit>

//---

export function fetchApi(resource: FetchResource): Promise<FetchApi>
export function fetchApi<FetchSome extends FetchApi>(resource: FetchResource): Promise<FetchSome> {
  const request: RequestInit = {
    method: 'GET',
    headers: {Accept: 'application/json'},
    //body: JSON.stringify({}),
    mode: 'no-cors',
    credentials: 'omit',
    cache: 'no-store',
  }
  return fetch(Config.FetchApi + resource, request)
      .then(response => {
        return response.json()
            .then(json => {
              const res: FetchApi = {json, error: response.status < 300 ? undefined
                    : response.status + ': ' + response.statusText + '. '
                    + (!json?.title ? '' : json?.title + '. ')
                    + (!json?.class || !json?.detail ? '' : json?.class + ': ' + json?.detail)}
              return res as FetchSome
            })
            .catch(error => {
              console.log(response, error)
              const res: FetchApi = {error: (response.status < 300 ? '' : response.status + ': ' + response.statusText + '. ')
                    + (error?.toString() ?? error?.message ?? error)}
              return res as FetchSome
            })
      })
      .catch(error => {
        console.log(error)
        const res: FetchApi = {error: error?.toString() ?? error?.message ?? error}
        return res as FetchSome
      })
}
