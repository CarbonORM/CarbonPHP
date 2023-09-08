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
import {C6, iWp_Users, wp_users, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iWp_Users, {}, iGetC6RestResponse<iWp_Users>, RestShortTableNames>({
    C6: C6,
    tableName: wp_users.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received wp users!'
        request.error ??= 'An unknown issue occurred creating the wp users!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iWp_Users>(response?.data?.rest, "wp_users", C6.wp_users.PRIMARY_SHORT as (keyof iWp_Users)[])
    }
})

export const Put = restRequest<{}, iWp_Users, {}, iPutC6RestResponse<iWp_Users>, RestShortTableNames>({
    C6: C6,
    tableName: wp_users.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated wp users!'
        request.error ??= 'An unknown issue occurred updating the wp users!'
        return request
    },
    responseCallback: (response, request) => {
        updateRestfulObjectArrays<iWp_Users>([
            removeInvalidKeys<iWp_Users>({
                ...request,
                ...response?.data?.rest,
            }, C6.TABLES)
        ], "wp_users", wp_users.PRIMARY_SHORT as (keyof iWp_Users)[])
    }
})


export const Post = restRequest<{}, iWp_Users, {}, iPostC6RestResponse<iWp_Users>, RestShortTableNames>({
    C6: C6,
    tableName: wp_users.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the wp users!'
        request.error ??= 'An unknown issue occurred creating the wp users!'
        return request
    },
    responseCallback: (response, request, id) => {
        if ('number' === typeof id || 'string' === typeof id) {
            if (1 !== wp_users.PRIMARY_SHORT.length) {
                console.error("C6 received unexpected result's given the primary key length");
            } else {
                request[wp_users.PRIMARY_SHORT[0]] = id
            }
        }
        updateRestfulObjectArrays<iWp_Users>(
            undefined !== request.dataInsertMultipleRows
                ? request.dataInsertMultipleRows.map((request, index) => {
                    return removeInvalidKeys<iWp_Users>({
                        ...request,
                        ...(index === 0 ? response?.data?.rest : {}),
                    }, C6.TABLES)
                })
                : [
                    removeInvalidKeys<iWp_Users>({
                        ...request,
                        ...response?.data?.rest,
                    }, C6.TABLES)
                ]
            , "wp_users", wp_users.PRIMARY_SHORT as (keyof iWp_Users)[])
    }
})

export const Delete = restRequest<{}, iWp_Users, {}, iDeleteC6RestResponse<iWp_Users>, RestShortTableNames>(
    {
        C6: C6,
        tableName: wp_users.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the wp users!'
            request.error ??= 'An unknown issue occurred removing the wp users!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iWp_Users>([
                request
            ], "wp_users", wp_users.PRIMARY_SHORT as (keyof iWp_Users)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
