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
import {C6, iLocation_References, location_references, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iLocation_References, {}, iGetC6RestResponse<iLocation_References>, RestShortTableNames>({
    C6: C6,
    tableName: location_references.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received location references!'
        request.error ??= 'An unknown issue occurred creating the location references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iLocation_References>(response?.data?.rest, "location_references", C6.location_references.PRIMARY_SHORT as (keyof iLocation_References)[])
    }
})

export const Put = restRequest<{}, iLocation_References, {}, iPutC6RestResponse<iLocation_References>, RestShortTableNames>({
    C6: C6,
    tableName: location_references.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated location references!'
        request.error ??= 'An unknown issue occurred updating the location references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iLocation_References>([
            removeInvalidKeys<iLocation_References>(response?.data?.rest, C6.TABLES)
        ], "location_references", location_references.PRIMARY_SHORT as (keyof iLocation_References)[])
    }
})


export const Post = restRequest<{}, iLocation_References, {}, iPostC6RestResponse<iLocation_References>, RestShortTableNames>({
    C6: C6,
    tableName: location_references.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the location references!'
        request.error ??= 'An unknown issue occurred creating the location references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iLocation_References>([
            removeInvalidKeys<iLocation_References>(response?.data?.rest, C6.TABLES)
        ], "location_references", location_references.PRIMARY_SHORT as (keyof iLocation_References[])
    }
})

export const Delete = restRequest<{}, iLocation_References, {}, iDeleteC6RestResponse<iLocation_References>, RestShortTableNames>(
    {
        C6: C6,
        tableName: location_references.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the location references!'
            request.error ??= 'An unknown issue occurred removing the location references!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iLocation_References>([
                request
            ], "location_references", location_references.PRIMARY_SHORT as (keyof iLocation_References)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
