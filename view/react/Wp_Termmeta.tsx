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
import {C6, iWp_Termmeta, wp_termmeta, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Termmeta, {}, iGetC6RestResponse<iWp_Termmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_termmeta.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp termmeta!'
        request.error ??= 'An unknown issue occurred creating the wp termmeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Termmeta>(response?.data?.rest, "wp_termmeta", C6.wp_termmeta.PRIMARY_SHORT as (keyof iWp_Termmeta)[])
    }
})

export const Put = restRequest<{}, iWp_Termmeta, {}, iPutC6RestResponse<iWp_Termmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_termmeta.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp termmeta!'
        request.error ??= 'An unknown issue occurred updating the wp termmeta!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Termmeta>([
            removeInvalidKeys<iWp_Termmeta>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_termmeta", wp_termmeta.PRIMARY_SHORT as (keyof iWp_Termmeta)[])
    }
})


export const Post = restRequest<{}, iWp_Termmeta, {}, iPostC6RestResponse<iWp_Termmeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_termmeta.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp termmeta!'
        request.error ??= 'An unknown issue occurred creating the wp termmeta!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_termmeta.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_termmeta.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Termmeta>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Termmeta>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Termmeta>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_termmeta", wp_termmeta.PRIMARY_SHORT as (keyof iWp_Termmeta)[])
    }
})

export const Delete = restRequest<{}, iWp_Termmeta, {}, iDeleteC6RestResponse<iWp_Termmeta>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_termmeta.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp termmeta!'
            request.error ??= 'An unknown issue occurred removing the wp termmeta!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Termmeta>([
                request
            ], "wp_termmeta", wp_termmeta.PRIMARY_SHORT as (keyof iWp_Termmeta)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
