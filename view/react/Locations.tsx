import {
    iPostC6RestResponse,
    restRequest,
    GET,
    POST,
    PUT,
    DELETE,
    iDeleteC6RestResponse,
    iGetC6RestResponse,
    iPutC6RestResponse,
    removeInvalidKeys
} from "@carbonorm/carbonnode";
import {deleteRestfulObjectArrays, updateRestfulObjectArrays} from "@carbonorm/carbonreact";
import {C6, iLocations, locations, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iLocations, {}, iGetC6RestResponse<iLocations>, RestShortTableNames>({
    C6: C6,
    tableName: locations.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received locations!'
        request.error ??= 'An unknown issue occurred creating the locations!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iLocations>(response?.data?.rest, "locations", C6.locations.PRIMARY_SHORT as (keyof iLocations)[])
    }
})

export const Put = restRequest<{}, iLocations, {}, iPutC6RestResponse<iLocations>, RestShortTableNames>({
    C6: C6,
    tableName: locations.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated locations!'
        request.error ??= 'An unknown issue occurred updating the locations!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iLocations>([
            removeInvalidKeys<iLocations>(response?.data?.rest, C6.TABLES)
        ], "locations", locations.PRIMARY_SHORT as (keyof iLocations)[])
    }
})


export const Post = restRequest<{}, iLocations, {}, iPostC6RestResponse<iLocations>, RestShortTableNames>({
    C6: C6,
    tableName: locations.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the locations!'
        request.error ??= 'An unknown issue occurred creating the locations!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iLocations>([
            removeInvalidKeys<iLocations>(response?.data?.rest, C6.TABLES)
        ], "locations", locations.PRIMARY_SHORT as (keyof iLocations[])
    }
})

export const Delete = restRequest<{}, iLocations, {}, iDeleteC6RestResponse<iLocations>, RestShortTableNames>(
    {
        C6: C6,
        tableName: locations.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the locations!'
            request.error ??= 'An unknown issue occurred removing the locations!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iLocations>([
                request
            ], "locations", locations.PRIMARY_SHORT as (keyof iLocations)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
