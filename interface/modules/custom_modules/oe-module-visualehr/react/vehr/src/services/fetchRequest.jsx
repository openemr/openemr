
import { fromFetch } from "rxjs/fetch";
import { switchMap, of, catchError } from "rxjs";
import { rxService } from './Services';
import { UtilService } from './UtilService';

export const fetchService = {
    fetchTimelines: (id) => fromFetch(`https://medsov.com/api/issues.php?pid=${id}`, {
                                method: "GET"
                            })
                        .pipe(
                            switchMap((response) => response.json()),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                console.error(err);
                                return of({ error: true, message: err.message })
                            })
                        )
                        .subscribe({
                                next: result => rxService.sendTimelines(result),
                                complete: () => {}
                            }),
    fetchSiberData: () =>fromFetch(`https://medsov.com/api/fetch.php`, {
                                method: "GET"
                         }).pipe(
                                switchMap((response) => response.json()),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                console.error(err);
                                return of({ error: true, message: err.message })
                            })
                        ).subscribe({
                                next: result => {rxService.sendTimelines(result)
                                    fetchService.fetchTimelines(UtilService.getPid())
                                },
                                complete: () =>{}
                            }),
    fetchICD10Lists:(search)=>fromFetch(`https://medsov.com/api/icd10.php/${search}`, {
                        method: "GET"
                        }).pipe(
                                switchMap((response) => response.json()),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                console.error(err);
                                return of({ error: true, message: err.message })
                            })
                        ),
    fetchCurrentICD10:(pid)=>fromFetch(`https://medsov.com/api/active_codes.php?pid=${pid}`, {
                        method: "GET"
                        }).pipe(
                                switchMap((response) => response.json()),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                console.error(err);
                                return of({ error: true, message: err.message })
                            })
                        ),
    fetchDropdowns:()=>fromFetch(`https://medsov.com/api/dropdown.php`, {
                        method: "GET"
                        }).pipe(
                                switchMap((response) => response.json()),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                console.error(err);
                                return of({ error: true, message: err.message })
                            })
                        ),
    fetchFavorites:()=>fromFetch(`https://medsov.com/api/favorites.php`, {
                        method: "GET"
                        }).pipe(
                                switchMap((response) => response.json()),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                console.error(err);
                                return of({ error: true, message: err.message })
                            })
                        ),                    
     storeRequst:(data,method)=>fromFetch(`https://medsov.com/api/store_data.php`, {
                        method: method,
                        headers:{
                            'Access-Control-Allow-Origin': '*',
                            'Content-Type': 'application/json'
                        },
                        body:JSON.stringify(data),
                        }).pipe(
                                switchMap((response) => {
                                    console.log(data)
                                    if (response.ok) {
                                        // OK return data
                                        return response.json();

                                      } else {
                                        // Server is returning a status requiring the client to try something else.
                                        return of({ error: true, message: `Error ${ response.status }` });
                                      }
                                }),
                            catchError(err => {
                                // Network or other error, handle appropriately
                                return of({ error: true, message: err.message })
                            })
                        ),                                                  
};
