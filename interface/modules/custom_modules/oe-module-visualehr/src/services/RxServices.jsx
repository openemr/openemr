import { fromFetch } from "rxjs/fetch";
import { switchMap, of, catchError } from "rxjs";
import { rxService } from "./Services";
import { UtilService } from "./UtilService";

export const fetchService = {
  getPatient: (id) =>
    fromFetch(`../api/patient.php?pid=${id}`, {
      method: "GET",
    })
      .pipe(
        switchMap((response) => response.json()),
        catchError((err) => {
          // Network or other error, handle appropriately
          console.error(err);
          return of({ error: true, message: err.message });
        })
      )
      .subscribe({
        next: (result) => rxService.sendPatientData(result),
        complete: () => {},
      }),

  fetchTimelines: (id) =>
    fromFetch(`../api/issues.php?pid=${id}`, {
      method: "GET",
    })
      .pipe(
        switchMap((response) => response.json()),
        catchError((err) => {
          // Network or other error, handle appropriately
          console.error(err);
          return of({ error: true, message: err.message });
        })
      )
      .subscribe({
        next: (result) => rxService.sendTimelines(result),
        complete: () => {},
      }),

  fetchSiberData: () =>
    fromFetch(`../api/fetch.php`, {
      method: "GET",
    })
      .pipe(
        switchMap((response) => response.json()),
        catchError((err) => {
          // Network or other error, handle appropriately
          console.error(err);
          return of({ error: true, message: err.message });
        })
      )
      .subscribe({
        next: (result) => {
          rxService.sendTimelines(result);
          fetchService.fetchTimelines(UtilService.getPid());
        },
        complete: () => {},
      }),

  fetchICD10Lists: (search) =>
    fromFetch(`../api/icd10.php/${search}`, {
      method: "GET",
    }).pipe(
      switchMap((response) => response.json()),
      catchError((err) => {
        // Network or other error, handle appropriately
        console.error(err);
        return of({ error: true, message: err.message });
      })
    ),

  fetchCurrentICD10: (pid) =>
    fromFetch(`../api/active_codes.php?pid=${pid}`, {
      method: "GET",
    }).pipe(
      switchMap((response) => response.json()),
      catchError((err) => {
        // Network or other error, handle appropriately
        console.error(err);
        return of({ error: true, message: err.message });
      })
    ),

  fetchDropdowns: () =>
    fromFetch(`../api/dropdown.php`, {
      method: "GET",
    }).pipe(
      switchMap((response) => response.json()),
      catchError((err) => {
        // Network or other error, handle appropriately
        console.error(err);
        return of({ error: true, message: err.message });
      })
    ),

  fetchFavorites: () =>
    fromFetch(`../api/favorites.php`, {
      method: "GET",
    }).pipe(
      switchMap((response) => response.json()),
      catchError((err) => {
        // Network or other error, handle appropriately
        console.error(err);
        return of({ error: true, message: err.message });
      })
    ),

  storeRequst: (data, method) =>
    fromFetch(`../api/store_data.php`, {
      method: method,
      headers: {
        "Access-Control-Allow-Origin": "*",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    }).pipe(
      switchMap((response) => {
        console.log(data);
        if (response.ok) {
          // OK return data
          return response.json();
        } else {
          // Server is returning a status requiring the client to try something else.
          return of({ error: true, message: `Error ${response.status}` });
        }
      }),
      catchError((err) => {
        // Network or other error, handle appropriately
        return of({ error: true, message: err.message });
      })
    ),
};

// ../api
