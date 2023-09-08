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
import {C6, iWp_Usermeta, wp_usermeta, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Usermeta, {}, iGetC6RestResponse<iWp_Usermeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_usermeta.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp usermeta!'
        request.error ??= 'An unknown issue occurred creating the wp usermeta!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Usermeta>(response?.data?.rest, "wp_usermeta", C6.wp_usermeta.PRIMARY_SHORT as (keyof iWp_Usermeta)[])
    }
})

export const Put = restRequest<{}, iWp_Usermeta, {}, iPutC6RestResponse<iWp_Usermeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_usermeta.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp usermeta!'
        request.error ??= 'An unknown issue occurred updating the wp usermeta!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Usermeta>([
            removeInvalidKeys<iWp_Usermeta>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_usermeta", wp_usermeta.PRIMARY_SHORT as (keyof iWp_Usermeta)[])
    }
})


export const Post = restRequest<{}, iWp_Usermeta, {}, iPostC6RestResponse<iWp_Usermeta>, RestShortTableNames>({
    C6: C6,
    tableName: wp_usermeta.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp usermeta!'
        request.error ??= 'An unknown issue occurred creating the wp usermeta!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_usermeta.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_usermeta.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Usermeta>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Usermeta>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Usermeta>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_usermeta", wp_usermeta.PRIMARY_SHORT as (keyof iWp_Usermeta)[])
    }
})

export const Delete = restRequest<{}, iWp_Usermeta, {}, iDeleteC6RestResponse<iWp_Usermeta>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_usermeta.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp usermeta!'
            request.error ??= 'An unknown issue occurred removing the wp usermeta!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Usermeta>([
                request
            ], "wp_usermeta", wp_usermeta.PRIMARY_SHORT as (keyof iWp_Usermeta)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
