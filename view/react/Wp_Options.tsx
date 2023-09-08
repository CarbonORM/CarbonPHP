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
import {C6, iWp_Options, wp_options, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Options, {}, iGetC6RestResponse<iWp_Options>, RestShortTableNames>({
    C6: C6,
    tableName: wp_options.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp options!'
        request.error ??= 'An unknown issue occurred creating the wp options!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Options>(response?.data?.rest, "wp_options", C6.wp_options.PRIMARY_SHORT as (keyof iWp_Options)[])
    }
})

export const Put = restRequest<{}, iWp_Options, {}, iPutC6RestResponse<iWp_Options>, RestShortTableNames>({
    C6: C6,
    tableName: wp_options.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp options!'
        request.error ??= 'An unknown issue occurred updating the wp options!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Options>([
            removeInvalidKeys<iWp_Options>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_options", wp_options.PRIMARY_SHORT as (keyof iWp_Options)[])
    }
})


export const Post = restRequest<{}, iWp_Options, {}, iPostC6RestResponse<iWp_Options>, RestShortTableNames>({
    C6: C6,
    tableName: wp_options.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp options!'
        request.error ??= 'An unknown issue occurred creating the wp options!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_options.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_options.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Options>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Options>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Options>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_options", wp_options.PRIMARY_SHORT as (keyof iWp_Options)[])
    }
})

export const Delete = restRequest<{}, iWp_Options, {}, iDeleteC6RestResponse<iWp_Options>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_options.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp options!'
            request.error ??= 'An unknown issue occurred removing the wp options!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Options>([
                request
            ], "wp_options", wp_options.PRIMARY_SHORT as (keyof iWp_Options)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
