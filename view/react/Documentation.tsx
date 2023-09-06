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
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iDocumentation>([
            removeInvalidKeys<iDocumentation>(response?.data?.rest, C6.TABLES)
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
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iDocumentation>([
            removeInvalidKeys<iDocumentation>(response?.data?.rest, C6.TABLES)
        ], "documentation", documentation.PRIMARY_SHORT as (keyof iDocumentation[])
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
