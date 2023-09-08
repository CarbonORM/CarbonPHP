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
import {C6, iDocumentation, documentation, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iDocumentation, {}, iGetC6RestResponse<iDocumentation>, RestShortTableNames>({
    C6: C6,
    tableName: documentation.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received documentation!'
        request.error ??= 'An unknown issue occurred creating the documentation!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iDocumentation>(response?.data?.rest, "documentation", C6.documentation.PRIMARY_SHORT as (keyof iDocumentation)[])
    }
})

export const Put = restRequest<{}, iDocumentation, {}, iPutC6RestResponse<iDocumentation>, RestShortTableNames>({
    C6: C6,
    tableName: documentation.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated documentation!'
        request.error ??= 'An unknown issue occurred updating the documentation!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iDocumentation>([
            removeInvalidKeys<iDocumentation>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "documentation", documentation.PRIMARY_SHORT as (keyof iDocumentation)[])
    }
})


export const Post = restRequest<{}, iDocumentation, {}, iPostC6RestResponse<iDocumentation>, RestShortTableNames>({
    C6: C6,
    tableName: documentation.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the documentation!'
        request.error ??= 'An unknown issue occurred creating the documentation!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== documentation.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[documentation.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iDocumentation>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iDocumentation>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iDocumentation>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "documentation", documentation.PRIMARY_SHORT as (keyof iDocumentation)[])
    }
})

export const Delete = restRequest<{}, iDocumentation, {}, iDeleteC6RestResponse<iDocumentation>, RestShortTableNames>(
    {
        C6: C6,
        tableName: documentation.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the documentation!'
            request.error ??= 'An unknown issue occurred removing the documentation!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iDocumentation>([
                request
            ], "documentation", documentation.PRIMARY_SHORT as (keyof iDocumentation)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
