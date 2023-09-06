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
import {C6, iGroup_References, group_references, RestShortTableNames} from "./C6";


export const Get = restRequest<{}, iGroup_References, {}, iGetC6RestResponse<iGroup_References>, RestShortTableNames>({
    C6: C6,
    tableName: group_references.TABLE_NAME,
    requestMethod: GET,
    queryCallback: (request) => {
        request.success ??= 'Successfully received group references!'
        request.error ??= 'An unknown issue occurred creating the group references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iGroup_References>(response?.data?.rest, "group_references", C6.group_references.PRIMARY_SHORT as (keyof iGroup_References)[])
    }
})

export const Put = restRequest<{}, iGroup_References, {}, iPutC6RestResponse<iGroup_References>, RestShortTableNames>({
    C6: C6,
    tableName: group_references.TABLE_NAME,
    requestMethod: PUT,
    queryCallback: (request) => {
        request.success ??= 'Successfully updated group references!'
        request.error ??= 'An unknown issue occurred updating the group references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iGroup_References>([
            removeInvalidKeys<iGroup_References>(response?.data?.rest, C6.TABLES)
        ], "group_references", group_references.PRIMARY_SHORT as (keyof iGroup_References)[])
    }
})


export const Post = restRequest<{}, iGroup_References, {}, iPostC6RestResponse<iGroup_References>, RestShortTableNames>({
    C6: C6,
    tableName: group_references.TABLE_NAME,
    requestMethod: POST,
    queryCallback: (request) => {
        request.success ??= 'Successfully created the group references!'
        request.error ??= 'An unknown issue occurred creating the group references!'
        return request
    },
    responseCallback: (response, _request) => {
        updateRestfulObjectArrays<iGroup_References>([
            removeInvalidKeys<iGroup_References>(response?.data?.rest, C6.TABLES)
        ], "group_references", group_references.PRIMARY_SHORT as (keyof iGroup_References[])
    }
})

export const Delete = restRequest<{}, iGroup_References, {}, iDeleteC6RestResponse<iGroup_References>, RestShortTableNames>(
    {
        C6: C6,
        tableName: group_references.TABLE_NAME,
        requestMethod: DELETE,
        queryCallback: (request) => {
            request.success ??= 'Successfully removed the group references!'
            request.error ??= 'An unknown issue occurred removing the group references!'
            return request
        },
        responseCallback: (_response, request) => {
            // todo - request . where
            deleteRestfulObjectArrays<iGroup_References>([
                request
            ], "group_references", group_references.PRIMARY_SHORT as (keyof iGroup_References)[])
        }
    });


export default {
    Get, Post, Put, Delete
}
