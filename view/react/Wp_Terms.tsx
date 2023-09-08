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
import {C6, iWp_Terms, wp_terms, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Terms, {}, iGetC6RestResponse<iWp_Terms>, RestShortTableNames>({
    C6: C6,
    tableName: wp_terms.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp terms!'
        request.error ??= 'An unknown issue occurred creating the wp terms!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Terms>(response?.data?.rest, "wp_terms", C6.wp_terms.PRIMARY_SHORT as (keyof iWp_Terms)[])
    }
})

export const Put = restRequest<{}, iWp_Terms, {}, iPutC6RestResponse<iWp_Terms>, RestShortTableNames>({
    C6: C6,
    tableName: wp_terms.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp terms!'
        request.error ??= 'An unknown issue occurred updating the wp terms!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Terms>([
            removeInvalidKeys<iWp_Terms>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_terms", wp_terms.PRIMARY_SHORT as (keyof iWp_Terms)[])
    }
})


export const Post = restRequest<{}, iWp_Terms, {}, iPostC6RestResponse<iWp_Terms>, RestShortTableNames>({
    C6: C6,
    tableName: wp_terms.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp terms!'
        request.error ??= 'An unknown issue occurred creating the wp terms!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_terms.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_terms.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Terms>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Terms>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Terms>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_terms", wp_terms.PRIMARY_SHORT as (keyof iWp_Terms)[])
    }
})

export const Delete = restRequest<{}, iWp_Terms, {}, iDeleteC6RestResponse<iWp_Terms>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_terms.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp terms!'
            request.error ??= 'An unknown issue occurred removing the wp terms!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Terms>([
                request
            ], "wp_terms", wp_terms.PRIMARY_SHORT as (keyof iWp_Terms)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
